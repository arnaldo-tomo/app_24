<?php
// app/Http/Controllers/Admin/AdminController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        // $this->middleware(function ($request, $next) {
        //     if (!auth()->user() || !auth()->user()->isAdmin()) {
        //         abort(403, 'Acesso negado');
        //     }
        //     return $next($request);
        // });
    }

    public function dashboard()
    {
        $stats = [
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'active_restaurants' => Restaurant::where('is_active', true)->count(),
            'online_delivery_persons' => User::where('role', 'delivery_person')
                                             ->where('is_active', true)
                                             ->count(),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'revenue_today' => Order::whereDate('created_at', today())
                                   ->where('payment_status', 'paid')
                                   ->sum('total_amount'),
        ];

        $recent_orders = Order::with(['customer', 'restaurant'])
                             ->latest()
                             ->take(10)
                             ->get();

        $top_restaurants = Restaurant::with(['orders' => function($query) {
                                        $query->whereDate('created_at', today());
                                    }])
                                    ->where('is_active', true)
                                    ->withCount(['orders as orders_today' => function($query) {
                                        $query->whereDate('created_at', today());
                                    }])
                                    ->orderBy('orders_today', 'desc')
                                    ->take(10)
                                    ->get();

        return view('admin.dashboard', compact('stats', 'recent_orders', 'top_restaurants'));
    }

    public function restaurants()
    {
        $restaurants = Restaurant::with('owner')
                                ->withCount('orders')
                                ->paginate(20);

        return view('admin.restaurants.index', compact('restaurants'));
    }

    public function orders()
    {
        $orders = Order::with(['customer', 'restaurant', 'deliveryPerson'])
                      ->latest()
                      ->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function customers()
    {
        $customers = User::where('role', 'customer')
                        ->withCount('orders')
                        ->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }

    public function deliveryPersons()
    {
        $delivery_persons = User::where('role', 'delivery_person')
                               ->withCount('deliveries')
                               ->paginate(20);

        return view('admin.delivery-persons.index', compact('delivery_persons'));
    }
}