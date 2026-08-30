<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get aggregate sales report with payment breakdowns.
     */
    public function sales(Request $request): JsonResponse
    {
        $query = Order::query();

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $totalSales = (float) (clone $query)->sum('total_amount');
        $totalOrders = (int) (clone $query)->count();
        $totalDiscount = (float) (clone $query)->sum('discount_amount');
        $totalTax = (float) (clone $query)->sum('tax_amount');
        $averageOrderValue = $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0.0;

        // Payment method breakdown
        $paymentMethods = (clone $query)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->payment_method,
                    'count'  => (int) $item->count,
                    'total'  => (float) $item->total,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'summary' => [
                    'total_sales'         => $totalSales,
                    'total_orders'        => $totalOrders,
                    'total_discount'      => $totalDiscount,
                    'total_tax'           => $totalTax,
                    'average_order_value' => $averageOrderValue,
                ],
                'payment_breakdown' => $paymentMethods,
            ],
        ]);
    }

    /**
     * Get list of best-selling products.
     */
    public function bestSellingProducts(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 10);

        $products = OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity_sold'),
                DB::raw('SUM(total_price) as total_revenue_generated')
            )
            ->with(['product.category'])
            ->groupBy('product_id')
            ->orderByDesc('total_quantity_sold')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id'              => $item->product_id,
                    'name'                    => $item->product ? $item->product->name : 'Deleted Product',
                    'category_name'           => $item->product && $item->product->category ? $item->product->category->name : 'General',
                    'unit_price'              => $item->product ? (float) $item->product->price : 0.0,
                    'total_quantity_sold'     => (int) $item->total_quantity_sold,
                    'total_revenue_generated' => (float) $item->total_revenue_generated,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }
}
