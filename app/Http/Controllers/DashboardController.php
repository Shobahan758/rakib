<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SiteVisit;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dasgboard.pages.index', [
            'orders' => Order::where('status', '!=', 'fake')->latest()->paginate(15),
            'orderCount' => Order::where('status', '!=', 'fake')->count(),
            'pendingCount' => Order::where('status', 'pending')->count(),
            'totalSales' => Order::whereNotIn('status', ['fake', 'refunded'])->sum('total'),
            'visitorCount' => SiteVisit::query()->distinct()->count('visitor_hash'),
            'todayVisitorCount' => SiteVisit::whereDate('visited_on', today())->count(),
        ]);
    }
}
