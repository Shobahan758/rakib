<?php

namespace App\Http\Controllers;

use App\Models\IncompleteOrder;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrderController extends Controller
{
    public function create(Request $request): View
    {
        $returnTo = $this->validOrderReturnTo($request->query('return_to'));
        $unitPrice = Product::where('is_active', true)->where('is_modal_product', false)->orderBy('sort_order')->value('price') ?? 0;

        return view('dasgboard.pages.orders.create', compact('returnTo', 'unitPrice'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateOrderData($request);
        $returnTo = $validated['return_to'];
        unset($validated['return_to']);
        $validated['total'] = (int) $validated['quantity'] * (int) $validated['unit_price'];
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = mb_substr((string) $request->userAgent(), 0, 500);
        Order::create($validated);

        $destination = match ($validated['status']) {
            'shipping' => 'shipping',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'fake' => 'fake_all',
            default => in_array($returnTo, ['all', 'today'], true) ? $returnTo : 'all',
        };

        return $this->redirectToOrderList($destination, 'Order created successfully.');
    }

    public function index(Request $request, string $filter = 'all'): View
    {
        abort_unless(in_array($filter, ['all', 'today', 'shipping', 'delivered', 'cancelled', 'refunded'], true), 404);

        $orders = Order::query()->with('deliveryAddons')->where('status', '!=', 'fake')->latest();
        match ($filter) {
            'all' => $orders->where('status', 'pending'),
            'today' => $orders->where('status', 'pending')->whereDate('created_at', today()),
            'shipping', 'delivered', 'cancelled', 'refunded' => $orders->where('status', $filter),
            default => null,
        };

        $labels = ['all' => 'All Orders', 'today' => "Today's Orders", 'shipping' => 'Shipping Orders', 'delivered' => 'Delivered Orders', 'cancelled' => 'Cancelled Orders', 'refunded' => 'Refunded Orders'];
        $viewAll = $request->boolean('view_all');

        return view('dasgboard.pages.orders.index', [
            'orders' => $orders->paginate($viewAll ? max(1, (clone $orders)->count()) : 10)->withQueryString(),
            'filter' => $filter,
            'pageTitle' => $labels[$filter],
            'viewAll' => $viewAll,
            'exportFilter' => $filter,
        ]);
    }

    public function fakeIndex(Request $request, string $filter = 'all'): View
    {
        abort_unless(in_array($filter, ['all', 'today'], true), 404);

        $orders = Order::query()->with('deliveryAddons')->where('status', 'fake')->latest();
        if ($filter === 'today') {
            $orders->whereDate('created_at', today());
        }

        $viewAll = $request->boolean('view_all');

        return view('dasgboard.pages.orders.index', [
            'orders' => $orders->paginate($viewAll ? max(1, (clone $orders)->count()) : 10)->withQueryString(),
            'filter' => $filter,
            'pageTitle' => $filter === 'today' ? "Today's Fake Orders" : 'All Fake Orders',
            'isFakeList' => true,
            'viewAll' => $viewAll,
            'exportFilter' => 'fake_'.$filter,
        ]);
    }

    public function export(string $filter): StreamedResponse
    {
        abort_unless(in_array($filter, ['all', 'today', 'shipping', 'delivered', 'cancelled', 'refunded', 'fake_all', 'fake_today'], true), 404);

        $orders = Order::query()->orderBy('id');
        match ($filter) {
            'all' => $orders->where('status', 'pending'),
            'today' => $orders->where('status', 'pending')->whereDate('created_at', today()),
            'shipping', 'delivered', 'cancelled', 'refunded' => $orders->where('status', $filter),
            'fake_all' => $orders->where('status', 'fake'),
            'fake_today' => $orders->where('status', 'fake')->whereDate('created_at', today()),
        };

        $safeCell = static function (mixed $value): mixed {
            if (! is_string($value)) {
                return $value;
            }

            return preg_match('/^[=+\-@]/u', $value) === 1 ? "'".$value : $value;
        };

        return response()->streamDownload(function () use ($orders, $safeCell): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Order ID', 'Parent Order ID', 'Order Date', 'Customer Name', 'Phone', 'Email',
                'Address', 'Product', 'Quantity', 'Unit Price', 'Delivery Area', 'Delivery Charge',
                'Total', 'Status', 'IP Address',
            ], ',', '"', '');

            $orders->chunkById(500, function ($chunk) use ($output, $safeCell): void {
                foreach ($chunk as $order) {
                    fputcsv($output, array_map($safeCell, [
                        $order->id,
                        $order->parent_order_id,
                        $order->created_at?->format('Y-m-d H:i:s'),
                        $order->name,
                        $order->phone,
                        $order->email,
                        $order->address,
                        $order->burger_type,
                        $order->quantity,
                        $order->unit_price,
                        $order->delivery_area,
                        $order->delivery_charge,
                        $order->total,
                        $order->status,
                        $order->ip_address,
                    ]), ',', '"', '');
                }
            });

            fclose($output);
        }, 'orders-'.$filter.'-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function edit(Request $request, Order $order): View
    {
        $returnTo = $this->validOrderReturnTo($request->query('return_to'));

        return view('dasgboard.pages.orders.edit', compact('order', 'returnTo'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $this->validateOrderData($request);

        $returnTo = $validated['return_to'];
        unset($validated['return_to']);
        $validated['total'] = (int) $validated['quantity'] * (int) $validated['unit_price'];
        $order->update($validated);

        return $this->redirectToOrderList($returnTo, 'Order updated successfully.');
    }

    public function destroy(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'return_to' => ['required', 'in:all,today,shipping,delivered,cancelled,refunded,fake_all,fake_today'],
        ]);
        abort_if($order->deliveryAddons()->exists(), 422, 'এই অর্ডারের সঙ্গে অতিরিক্ত পণ্য যুক্ত আছে। আগে সেগুলো সরান।');
        $order->delete();

        return $this->redirectToOrderList($validated['return_to'], 'Order deleted successfully.');
    }

    public function incompleteIndex(string $filter = 'all'): View
    {
        abort_unless(in_array($filter, ['all', 'today'], true), 404);

        $orders = IncompleteOrder::query()->latest('updated_at');
        if ($filter === 'today') {
            $orders->whereDate('created_at', today());
        }

        return view('dasgboard.pages.orders.incomplete', [
            'orders' => $orders->paginate(10)->withQueryString(),
            'filter' => $filter,
            'pageTitle' => $filter === 'today' ? "Today's Incomplete Orders" : 'All Incomplete Orders',
        ]);
    }

    public function editIncomplete(Request $request, IncompleteOrder $order): View
    {
        $filter = in_array($request->query('filter'), ['all', 'today'], true)
            ? $request->query('filter')
            : 'all';

        return view('dasgboard.pages.orders.edit-incomplete', compact('order', 'filter'));
    }

    public function updateIncomplete(Request $request, IncompleteOrder $order): RedirectResponse
    {
        $phone = str_replace(
            ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            (string) $request->input('phone')
        );
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if (str_starts_with($phone, '88') && strlen($phone) === 13) {
            $phone = substr($phone, 2);
        }

        $request->merge(['phone' => $phone]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'quantity' => ['required', 'integer', 'min:1'],
            'list_filter' => ['required', 'in:all,today'],
        ]);

        unset($validated['list_filter']);
        $order->update($validated);

        return redirect()
            ->route('admin.incomplete-orders.index', $request->input('list_filter'))
            ->with('success', 'Incomplete order updated successfully.');
    }

    public function destroyIncomplete(Request $request, IncompleteOrder $order): RedirectResponse
    {
        $validated = $request->validate(['list_filter' => ['required', 'in:all,today']]);
        abort_if($order->deliveryAddons()->exists(), 422, 'এই অর্ডারের সঙ্গে অতিরিক্ত পণ্য যুক্ত আছে। আগে সেগুলো সরান।');
        $order->delete();

        return redirect()
            ->route('admin.incomplete-orders.index', $validated['list_filter'])
            ->with('success', 'Incomplete order deleted successfully.');
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:pending,shipping,delivered,cancelled,refunded,fake']]);
        $order->update($validated);

        if ($validated['status'] === 'fake') {
            return redirect()
                ->route('admin.fake-orders.index', 'all')
                ->with('success', 'Order status updated successfully.');
        }

        $filter = match ($validated['status']) {
            'shipping' => 'shipping',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            default => 'all',
        };

        return redirect()
            ->route('admin.orders.index', $filter)
            ->with('success', 'Order status updated successfully.');
    }

    private function validOrderReturnTo(mixed $returnTo): string
    {
        $allowed = ['all', 'today', 'shipping', 'delivered', 'cancelled', 'refunded', 'fake_all', 'fake_today'];

        return in_array($returnTo, $allowed, true) ? $returnTo : 'all';
    }

    private function validateOrderData(Request $request): array
    {
        $phone = str_replace(
            ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            (string) $request->input('phone')
        );
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if (str_starts_with($phone, '88') && strlen($phone) === 13) {
            $phone = substr($phone, 2);
        }
        $request->merge(['phone' => $phone]);

        return $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'burger_type' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'unit_price' => ['required', 'integer', 'min:0', 'max:99999999'],
            'status' => ['required', 'in:pending,shipping,delivered,cancelled,refunded,fake'],
            'return_to' => ['required', 'in:all,today,shipping,delivered,cancelled,refunded,fake_all,fake_today'],
        ]);
    }

    private function redirectToOrderList(string $returnTo, string $message): RedirectResponse
    {
        if (str_starts_with($returnTo, 'fake_')) {
            return redirect()
                ->route('admin.fake-orders.index', substr($returnTo, 5))
                ->with('success', $message);
        }

        return redirect()
            ->route('admin.orders.index', $returnTo)
            ->with('success', $message);
    }
}
