<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get real-time dashboard KPIs, recent orders, and top selling items.
     */
    public function index(): JsonResponse
    {
        $settings = Setting::current();
        $today = Carbon::today();

        // Today's metrics
        $todayOrders = Order::whereDate('created_at', $today);
        $todaySales = (float) $todayOrders->sum('total_amount');
        $todayOrdersCount = (int) $todayOrders->count();

        // Product inventory metrics
        $activeProductsCount = Product::active()->count();
        $lowStockProductsCount = Product::where('stock_quantity', '<=', $settings->low_stock_threshold)
            ->where('stock_quantity', '>', 0)
            ->count();

        // Recent 5 orders
        $recentOrders = Order::with(['items.product', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        // Top 5 best selling items
        $topSellingProducts = OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id'    => $item->product_id,
                    'product_name'  => $item->product ? $item->product->name : 'Unknown Item',
                    'quantity_sold' => (int) $item->total_quantity,
                    'revenue'       => (float) $item->total_revenue,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'stats' => [
                    'today_sales'         => $todaySales,
                    'today_orders'        => $todayOrdersCount,
                    'total_products'      => $activeProductsCount,
                    'low_stock_products'  => $lowStockProductsCount,
                    'low_stock_threshold' => (int) $settings->low_stock_threshold,
                ],
                'recent_orders' => OrderResource::collection($recentOrders),
                'best_sellers'  => $topSellingProducts,
            ],
        ]);
    }
}
