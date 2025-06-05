<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Payment;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        $period = request('period', '30'); // 7, 30, 90, 365 days
        $startDate = now()->subDays($period)->startOfDay();
        $endDate = now()->endOfDay();

        $stats = $this->getOverviewStats($startDate, $endDate);
        $charts = $this->getChartData($startDate, $endDate, $period);

        return view('admin.reports.index', compact('stats', 'charts', 'period'));
    }

    public function sales()
    {
        $startDate = request('start_date') ? Carbon::parse(request('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = request('end_date') ? Carbon::parse(request('end_date'))->endOfDay() : now()->endOfDay();
        $groupBy = request('group_by', 'day'); // day, week, month

        $salesData = $this->getSalesData($startDate, $endDate, $groupBy);
        $topRestaurants = $this->getTopRestaurants($startDate, $endDate);
        $topMenuItems = $this->getTopMenuItems($startDate, $endDate);

        return view('admin.reports.sales', compact('salesData', 'topRestaurants', 'topMenuItems', 'startDate', 'endDate', 'groupBy'));
    }

    public function restaurants()
    {
        $startDate = request('start_date') ? Carbon::parse(request('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = request('end_date') ? Carbon::parse(request('end_date'))->endOfDay() : now()->endOfDay();

        $restaurantStats = Restaurant::with(['orders' => function($query) use ($startDate, $endDate) {
                                        $query->whereBetween('created_at', [$startDate, $endDate])
                                              ->where('status', '!=', 'cancelled');
                                    }])
                                   ->withCount(['orders as orders_count' => function($query) use ($startDate, $endDate) {
                                        $query->whereBetween('created_at', [$startDate, $endDate])
                                              ->where('status', '!=', 'cancelled');
                                    }])
                                   ->get()
                                   ->map(function($restaurant) {
                                        $orders = $restaurant->orders;
                                        return [
                                            'restaurant' => $restaurant,
                                            'orders_count' => $restaurant->orders_count,
                                            'total_revenue' => $orders->sum('total_amount'),
                                            'avg_order_value' => $orders->count() > 0 ? $orders->avg('total_amount') : 0,
                                            'completion_rate' => $this->getCompletionRate($restaurant->id, $startDate, $endDate),
                                        ];
                                   })
                                   ->sortByDesc('total_revenue');

        return view('admin.reports.restaurants', compact('restaurantStats', 'startDate', 'endDate'));
    }

    public function deliveries()
    {
        $startDate = request('start_date') ? Carbon::parse(request('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = request('end_date') ? Carbon::parse(request('end_date'))->endOfDay() : now()->endOfDay();

        $deliveryStats = User::where('role', 'delivery_person')
                            ->with(['deliveries' => function($query) use ($startDate, $endDate) {
                                $query->whereBetween('created_at', [$startDate, $endDate])
                                      ->whereIn('status', ['delivered', 'picked_up']);
                            }])
                            ->get()
                            ->map(function($deliveryPerson) use ($startDate, $endDate) {
                                $deliveries = $deliveryPerson->deliveries;
                                return [
                                    'delivery_person' => $deliveryPerson,
                                    'deliveries_count' => $deliveries->count(),
                                    'total_earnings' => $deliveries->sum('delivery_fee'),
                                    'avg_delivery_time' => $this->getAvgDeliveryTime($deliveryPerson->id, $startDate, $endDate),
                                    'completion_rate' => $this->getDeliveryCompletionRate($deliveryPerson->id, $startDate, $endDate),
                                ];
                            })
                            ->sortByDesc('deliveries_count');

        $deliveryMetrics = $this->getDeliveryMetrics($startDate, $endDate);

        return view('admin.reports.deliveries', compact('deliveryStats', 'deliveryMetrics', 'startDate', 'endDate'));
    }

    public function customers()
    {
        $startDate = request('start_date') ? Carbon::parse(request('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = request('end_date') ? Carbon::parse(request('end_date'))->endOfDay() : now()->endOfDay();

        $customerStats = User::where('role', 'customer')
                            ->with(['orders' => function($query) use ($startDate, $endDate) {
                                $query->whereBetween('created_at', [$startDate, $endDate])
                                      ->where('status', '!=', 'cancelled');
                            }])
                            ->withCount(['orders as orders_count' => function($query) use ($startDate, $endDate) {
                                $query->whereBetween('created_at', [$startDate, $endDate])
                                      ->where('status', '!=', 'cancelled');
                            }])
                            ->having('orders_count', '>', 0)
                            ->get()
                            ->map(function($customer) {
                                $orders = $customer->orders;
                                return [
                                    'customer' => $customer,
                                    'orders_count' => $customer->orders_count,
                                    'total_spent' => $orders->sum('total_amount'),
                                    'avg_order_value' => $orders->avg('total_amount'),
                                    'last_order' => $orders->max('created_at'),
                                ];
                            })
                            ->sortByDesc('total_spent');

        $customerMetrics = $this->getCustomerMetrics($startDate, $endDate);

        return view('admin.reports.customers', compact('customerStats', 'customerMetrics', 'startDate', 'endDate'));
    }

    private function getOverviewStats($startDate, $endDate)
    {
        $currentPeriodOrders = Order::whereBetween('created_at', [$startDate, $endDate])
                                  ->where('status', '!=', 'cancelled');

        $previousStart = $startDate->copy()->sub($endDate->diffInDays($startDate), 'days');
        $previousEnd = $startDate->copy()->subDay();

        $previousPeriodOrders = Order::whereBetween('created_at', [$previousStart, $previousEnd])
                                   ->where('status', '!=', 'cancelled');

        return [
            'total_orders' => [
                'current' => $currentPeriodOrders->count(),
                'previous' => $previousPeriodOrders->count(),
            ],
            'total_revenue' => [
                'current' => $currentPeriodOrders->sum('total_amount'),
                'previous' => $previousPeriodOrders->sum('total_amount'),
            ],
            'avg_order_value' => [
                'current' => $currentPeriodOrders->avg('total_amount') ?? 0,
                'previous' => $previousPeriodOrders->avg('total_amount') ?? 0,
            ],
            'new_customers' => [
                'current' => User::where('role', 'customer')
                                ->whereBetween('created_at', [$startDate, $endDate])
                                ->count(),
                'previous' => User::where('role', 'customer')
                                ->whereBetween('created_at', [$previousStart, $previousEnd])
                                ->count(),
            ],
        ];
    }

    private function getChartData($startDate, $endDate, $period)
    {
        $format = $period <= 7 ? '%Y-%m-%d %H:00:00' : '%Y-%m-%d';
        $groupFormat = $period <= 7 ? 'Y-m-d H' : 'Y-m-d';

        $ordersChart = Order::select(
                            DB::raw("DATE_FORMAT(created_at, '$format') as period"),
                            DB::raw('COUNT(*) as count'),
                            DB::raw('SUM(total_amount) as revenue')
                        )
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->where('status', '!=', 'cancelled')
                        ->groupBy('period')
                        ->orderBy('period')
                        ->get()
                        ->map(function($item) use ($groupFormat) {
                            return [
                                'period' => Carbon::createFromFormat($groupFormat === 'Y-m-d H' ? 'Y-m-d H' : 'Y-m-d',
                                           $groupFormat === 'Y-m-d H' ? $item->period : $item->period)
                                           ->format($groupFormat === 'Y-m-d H' ? 'd/m H:00' : 'd/m'),
                                'orders' => $item->count,
                                'revenue' => (float) $item->revenue,
                            ];
                        });

        $statusChart = Order::select('status', DB::raw('COUNT(*) as count'))
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->groupBy('status')
                          ->get()
                          ->map(function($item) {
                              return [
                                  'status' => ucfirst($item->status),
                                  'count' => $item->count,
                              ];
                          });

        return [
            'orders_revenue' => $ordersChart,
            'orders_status' => $statusChart,
        ];
    }

    private function getSalesData($startDate, $endDate, $groupBy)
    {
        $format = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        return Order::select(
                    DB::raw("DATE_FORMAT(created_at, '$format') as period"),
                    DB::raw('COUNT(*) as orders_count'),
                    DB::raw('SUM(total_amount) as total_revenue'),
                    DB::raw('SUM(delivery_fee) as delivery_revenue'),
                    DB::raw('AVG(total_amount) as avg_order_value')
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->groupBy('period')
                ->orderBy('period')
                ->get();
    }

    private function getTopRestaurants($startDate, $endDate, $limit = 10)
    {
        return Restaurant::select('restaurants.*')
                        ->selectSub(function($query) use ($startDate, $endDate) {
                            $query->from('orders')
                                  ->whereColumn('restaurant_id', 'restaurants.id')
                                  ->whereBetween('created_at', [$startDate, $endDate])
                                  ->where('status', '!=', 'cancelled')
                                  ->selectRaw('COALESCE(SUM(total_amount), 0)');
                        }, 'total_revenue')
                        ->selectSub(function($query) use ($startDate, $endDate) {
                            $query->from('orders')
                                  ->whereColumn('restaurant_id', 'restaurants.id')
                                  ->whereBetween('created_at', [$startDate, $endDate])
                                  ->where('status', '!=', 'cancelled')
                                  ->selectRaw('COUNT(*)');
                        }, 'orders_count')
                        ->orderBy('total_revenue', 'desc')
                        ->limit($limit)
                        ->get();
    }

    private function getTopMenuItems($startDate, $endDate, $limit = 10)
    {
        return MenuItem::select('menu_items.*')
                      ->selectSub(function($query) use ($startDate, $endDate) {
                          $query->from('order_items')
                                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                ->whereColumn('order_items.menu_item_id', 'menu_items.id')
                                ->whereBetween('orders.created_at', [$startDate, $endDate])
                                ->where('orders.status', '!=', 'cancelled')
                                ->selectRaw('COALESCE(SUM(order_items.quantity), 0)');
                      }, 'total_quantity')
                      ->selectSub(function($query) use ($startDate, $endDate) {
                          $query->from('order_items')
                                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                ->whereColumn('order_items.menu_item_id', 'menu_items.id')
                                ->whereBetween('orders.created_at', [$startDate, $endDate])
                                ->where('orders.status', '!=', 'cancelled')
                                ->selectRaw('COALESCE(SUM(order_items.total_price), 0)');
                      }, 'total_revenue')
                      ->with('restaurant')
                      ->orderBy('total_quantity', 'desc')
                      ->limit($limit)
                      ->get();
    }

    private function getCompletionRate($restaurantId, $startDate, $endDate)
    {
        $totalOrders = Order::where('restaurant_id', $restaurantId)
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->count();

        if ($totalOrders === 0) return 0;

        $completedOrders = Order::where('restaurant_id', $restaurantId)
                               ->whereBetween('created_at', [$startDate, $endDate])
                               ->where('status', 'delivered')
                               ->count();

        return round(($completedOrders / $totalOrders) * 100, 2);
    }

    private function getAvgDeliveryTime($deliveryPersonId, $startDate, $endDate)
    {
        $avgTime = Order::where('delivery_person_id', $deliveryPersonId)
                       ->whereBetween('created_at', [$startDate, $endDate])
                       ->where('status', 'delivered')
                       ->whereNotNull('delivered_at')
                       ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, delivered_at)) as avg_time')
                       ->value('avg_time');

        return $avgTime ? round($avgTime, 1) : 0;
    }

    private function getDeliveryCompletionRate($deliveryPersonId, $startDate, $endDate)
    {
        $assignedOrders = Order::where('delivery_person_id', $deliveryPersonId)
                             ->whereBetween('created_at', [$startDate, $endDate])
                             ->count();

        if ($assignedOrders === 0) return 0;

        $deliveredOrders = Order::where('delivery_person_id', $deliveryPersonId)
                              ->whereBetween('created_at', [$startDate, $endDate])
                              ->where('status', 'delivered')
                              ->count();

        return round(($deliveredOrders / $assignedOrders) * 100, 2);
    }

    private function getDeliveryMetrics($startDate, $endDate)
    {
        return [
            'avg_delivery_time' => Order::whereBetween('created_at', [$startDate, $endDate])
                                       ->where('status', 'delivered')
                                       ->whereNotNull('delivered_at')
                                       ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, delivered_at)) as avg_time')
                                       ->value('avg_time') ?? 0,
            'on_time_rate' => $this->getOnTimeDeliveryRate($startDate, $endDate),
        ];
    }

    private function getOnTimeDeliveryRate($startDate, $endDate)
    {
        $totalDeliveries = Order::whereBetween('created_at', [$startDate, $endDate])
                               ->where('status', 'delivered')
                               ->whereNotNull('delivered_at')
                               ->count();

        if ($totalDeliveries === 0) return 0;

        $onTimeDeliveries = Order::whereBetween('created_at', [$startDate, $endDate])
                                ->where('status', 'delivered')
                                ->whereNotNull('delivered_at')
                                ->whereRaw('delivered_at <= estimated_delivery_time')
                                ->count();

        return round(($onTimeDeliveries / $totalDeliveries) * 100, 2);
    }

    private function getCustomerMetrics($startDate, $endDate)
    {
        return [
            'new_customers' => User::where('role', 'customer')
                                 ->whereBetween('created_at', [$startDate, $endDate])
                                 ->count(),
            'repeat_customers' => User::where('role', 'customer')
                                    ->whereHas('orders', function($query) use ($startDate, $endDate) {
                                        $query->whereBetween('created_at', [$startDate, $endDate]);
                                    }, '>=', 2)
                                    ->count(),
            'customer_lifetime_value' => Order::whereBetween('created_at', [$startDate, $endDate])
                                             ->where('status', '!=', 'cancelled')
                                             ->selectRaw('AVG(customer_total) as clv')
                                             ->fromSub(function($query) use ($startDate, $endDate) {
                                                 $query->from('orders')
                                                       ->select('user_id', DB::raw('SUM(total_amount) as customer_total'))
                                                       ->whereBetween('created_at', [$startDate, $endDate])
                                                       ->where('status', '!=', 'cancelled')
                                                       ->groupBy('user_id');
                                             }, 'customer_totals')
                                             ->value('clv') ?? 0,
        ];
    }
}