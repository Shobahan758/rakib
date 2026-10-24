<?php

namespace App\Http\Controllers;

use App\Models\CourierBooking;
use App\Models\CourierSetting;
use App\Models\Order;
use App\Services\CourierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class CourierController extends Controller
{
    public function settings()
    {
        return view('dasgboard.pages.couriers.settings', ['settings' => CourierSetting::all()->keyBy('provider')]);
    }

    public function save(Request $request)
    {
        $rules = [];
        foreach (CourierSetting::FIELDS as $provider => $fields) {
            foreach ($fields as $field => $label) {
                $rules["$provider.$field"] = ['nullable', 'string', 'max:2000'];
                if ($field === 'store_id') {
                    $rules["$provider.$field"][] = 'regex:/^[1-9][0-9]*$/';
                }
                if ($field === 'username') {
                    $rules["$provider.$field"][] = 'email';
                }
            }
            if ($provider !== 'steadfast') {
                $rules["$provider.environment"] = ['required', 'in:live,sandbox'];
            }
        }
        $validator = Validator::make($request->all(), $rules);
        // Never flash credentials back into the session or render them into HTML.
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }
        DB::transaction(function () use ($validator) {
            foreach ($validator->validated() as $provider => $input) {
                $setting = CourierSetting::firstOrNew(['provider' => $provider]);
                $setting->credentials = array_replace($setting->credentials ?? [], array_filter($input, fn ($value) => filled($value)));
                $setting->save();
            }
        });

        return back()->with('success', 'Courier settings saved. Use Test Connection to verify access.');
    }

    public function check(string $provider, CourierService $service)
    {
        abort_unless(array_key_exists($provider, CourierSetting::FIELDS), 404);
        $setting = CourierSetting::where('provider', $provider)->first();
        try {
            $ok = $setting && $service->check($setting);
        } catch (Throwable) {
            $ok = false;
        }

        return back()->with($ok ? 'success' : 'error', $ok ? ucfirst($provider).' connection successful. No delivery was created.' : 'Connection failed. Check saved credentials, environment and courier access.');
    }

    public function locations(Request $request, CourierService $service)
    {
        $data = $request->validate([
            'provider' => ['required', 'in:pathao,redx'],
            'city' => ['nullable', 'integer', 'min:1'],
            'zone' => ['nullable', 'integer', 'min:1'],
        ]);
        $setting = CourierSetting::where('provider', $data['provider'])->first();
        if (! $setting || ! $setting->configured()) {
            return response()->json(['message' => 'Configure this courier first.'], 422);
        }
        $path = $data['provider'] === 'redx' ? '/areas' : (isset($data['zone'])
            ? '/aladdin/api/v1/zones/'.$data['zone'].'/area-list'
            : (isset($data['city']) ? '/aladdin/api/v1/cities/'.$data['city'].'/zone-list' : '/aladdin/api/v1/countries/1/city-list'));
        try {
            $response = $service->client($setting)->get($path);
            if (! $response->successful()) {
                throw new RuntimeException;
            }
            $items = $data['provider'] === 'redx' ? $response->json('areas') : $response->json('data.data');
            if (! is_array($items)) {
                throw new RuntimeException;
            }

            return response()->json(['locations' => array_map(fn ($item) => [
                'id' => $item['city_id'] ?? $item['zone_id'] ?? $item['area_id'] ?? $item['id'] ?? null,
                'name' => $item['city_name'] ?? $item['zone_name'] ?? $item['area_name'] ?? $item['name'] ?? '',
            ], $items)]);
        } catch (Throwable) {
            return response()->json(['message' => 'Could not load locations. Check the courier connection.'], 422);
        }
    }

    public function create(Order $order)
    {
        $order->load('deliveryAddons');

        return view('dasgboard.pages.couriers.book', [
            'order' => $order,
            'settings' => CourierSetting::all()->filter(fn ($setting) => $setting->configured()),
            'booking' => $order->courierBooking,
            'cod' => $order->total + $order->deliveryAddons->whereNotIn('status', ['cancelled', 'refunded', 'fake'])->sum('total'),
        ]);
    }

    public function book(Request $request, Order $order, CourierService $service)
    {
        $data = $request->validate([
            'provider' => ['required', 'in:steadfast,pathao,redx'],
            'weight' => ['nullable', 'required_unless:provider,steadfast', 'numeric', 'min:0.5', 'max:100'],
            'city_id' => ['nullable', 'required_if:provider,pathao', 'integer', 'min:1'],
            'zone_id' => ['nullable', 'required_if:provider,pathao', 'integer', 'min:1'],
            'area_id' => ['nullable', 'required_if:provider,redx', 'integer', 'min:1'],
            'area_name' => ['nullable', 'required_if:provider,redx', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        $setting = CourierSetting::where('provider', $data['provider'])->first();
        if (! $setting || ! $setting->configured()) {
            return back()->with('error', 'Configure this courier in Courier Settings first.');
        }
        try {
            $client = $service->client($setting);
        } catch (Throwable) {
            return back()->with('error', 'Courier authentication failed. Check Courier Settings and test the connection.');
        }
        try {
            [$booking, $payload] = DB::transaction(function () use ($order, $setting, $service, $data) {
                $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                if ($order->parent_order_id || ! in_array($order->status, ['pending', 'processing'], true)) {
                    throw new RuntimeException('Only pending parent orders can be booked.');
                }
                $existing = CourierBooking::where('order_id', $order->id)->first();
                if ($existing && $existing->status !== 'failed') {
                    throw new RuntimeException('This order is booked or awaiting verification. Check the courier merchant account before taking further action.');
                }
                $order->load(['deliveryAddons' => fn ($query) => $query->whereNotIn('status', ['cancelled', 'refunded', 'fake'])]);
                $total = $order->total + $order->deliveryAddons->sum('total');
                $payload = $service->payload($setting, $order, $data, $total);
                $booking = CourierBooking::updateOrCreate(['order_id' => $order->id], [
                    'provider' => $setting->provider, 'status' => 'submitting', 'cod_amount' => $total, 'message' => null,
                ]);

                return [$booking, $payload];
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        try {
            $response = $service->send($client, $setting->provider, $payload);
            $id = match ($setting->provider) {
                'steadfast' => $response->json('consignment.consignment_id'),
                'pathao' => $response->json('data.consignment_id'),
                'redx' => $response->json('tracking_id'),
            };
            $rejected = $response->clientError() || ($response->successful() && (
                in_array((int) $response->json('status'), [400, 401, 403, 422], true) || $response->json('type') === 'error'
            ));
            if ($response->successful() && filled($id) && ! $rejected) {
                DB::transaction(function () use ($booking, $order, $response, $id) {
                    $booking->update(['status' => 'booked', 'consignment_id' => (string) $id,
                        'tracking_code' => $response->json('consignment.tracking_code')]);
                    $order->update(['status' => 'shipping']);
                });

                return redirect()->route('admin.couriers.book', $order)->with('success', 'Courier booked successfully. Consignment: '.$id);
            }
            $message = $rejected ? 'Courier rejected the request. Check customer details, area IDs and saved credentials before retrying.' : 'Booking result is uncertain. Check the courier merchant account; do not create another delivery.';
            $booking->update(['status' => $rejected ? 'failed' : 'unknown', 'message' => $message]);
        } catch (Throwable) {
            $message = 'Booking result is uncertain. Check the courier merchant account; do not create another delivery.';
            $booking->update(['status' => 'unknown', 'message' => $message]);
        }

        return back()->with('error', $message);
    }
}
