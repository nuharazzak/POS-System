<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with search, payment method, and date filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['items.product', 'user'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->get();

        return response()->json([
            'success' => true,
            'data'    => OrderResource::collection($orders),
            'orders'  => OrderResource::collection($orders),
        ]);
    }

    /**
     * Display the specified order with receipt details.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['items.product', 'user']);
        return response()->json([
            'success' => true,
            'order'   => new OrderResource($order),
            'data'    => new OrderResource($order),
        ]);
    }

    /**
     * Execute atomic POS checkout inside a database transaction.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = DB::transaction(function () use ($request) {
            $settings = Setting::current();
            $taxRate = (float) $settings->tax_rate;
            $itemsData = $request->input('items', []);

            $productIds = collect($itemsData)->pluck('product_id')->unique();
            // Lock rows for update during checkout to guarantee atomic inventory consistency
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            $subtotal = 0.0;
            $preparedItems = [];

            // 1, 2, 3: Validate products, verify prices, check stock
            foreach ($itemsData as $item) {
                $productId = $item['product_id'];
                $requestedQty = (int) $item['quantity'];

                if (!$products->has($productId)) {
                    throw ValidationException::withMessages([
                        'items' => ["Product ID {$productId} was not found in the catalog."],
                    ]);
                }

                $product = $products->get($productId);

                if (!$product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => ["Product '{$product->name}' is currently unavailable."],
                    ]);
                }

                if ($product->stock_quantity < $requestedQty) {
                    throw ValidationException::withMessages([
                        'items' => ["Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}, Requested: {$requestedQty}."],
                    ]);
                }

                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * $requestedQty, 2);
                $subtotal += $lineTotal;

                $preparedItems[] = [
                    'product'     => $product,
                    'product_id'  => $productId,
                    'quantity'    => $requestedQty,
                    'unit_price'  => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            // 4, 5: Discount calculations
            $discountType = $request->input('discount_type', 'percentage');
            $discountValue = (float) $request->input('discount_value', 0);
            $discountAmount = 0.0;

            if ($discountValue > 0) {
                if ($discountType === 'percentage') {
                    $discountAmount = round($subtotal * (min(100, $discountValue) / 100), 2);
                } else {
                    $discountAmount = round(min($subtotal, $discountValue), 2);
                }
            }

            $discountedSubtotal = max(0.0, $subtotal - $discountAmount);

            // 6: Calculate Tax
            $taxAmount = round($discountedSubtotal * ($taxRate / 100), 2);

            // 7: Calculate Total
            $totalAmount = round($discountedSubtotal + $taxAmount, 2);

            // 8, 9: Validate Payment & Calculate Change
            $paymentMethod = $request->input('payment_method', 'cash');
            $amountReceived = (float) $request->input('amount_received', 0);
            $changeAmount = 0.0;

            if ($paymentMethod === 'cash') {
                if ($amountReceived < $totalAmount) {
                    throw ValidationException::withMessages([
                        'amount_received' => ["Cash received ($" . number_format($amountReceived, 2) . ") is less than the total amount ($" . number_format($totalAmount, 2) . ")."],
                    ]);
                }
                $changeAmount = round($amountReceived - $totalAmount, 2);
            } else {
                $amountReceived = $totalAmount;
                $changeAmount = 0.0;
            }

            // 13: Sequential order numbering (e.g. ORD-000001)
            $lastOrder = Order::latest('id')->first();
            $nextSequence = $lastOrder ? ($lastOrder->id + 1) : 1;
            $orderNumber = 'ORD-' . str_pad($nextSequence, 6, '0', STR_PAD_LEFT);

            // 10: Create Order
            $order = Order::create([
                'order_number'    => $orderNumber,
                'user_id'         => $request->user()->id,
                'subtotal'        => $subtotal,
                'discount_type'   => $discountType,
                'discount_value'  => $discountValue,
                'discount_amount' => $discountAmount,
                'tax_rate'        => $taxRate,
                'tax_amount'      => $taxAmount,
                'total_amount'    => $totalAmount,
                'payment_method'  => $paymentMethod,
                'amount_received' => $amountReceived,
                'change_amount'   => $changeAmount,
                'status'          => 'completed',
            ]);

            // 11, 12: Create Order Items & Deduct Stock
            foreach ($preparedItems as $item) {
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                // Atomic stock decrement
                $item['product']->decrement('stock_quantity', $item['quantity']);
            }

            return $order;
        });

        $order->load(['items.product', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Order completed successfully',
            'order'   => new OrderResource($order),
            'data'    => new OrderResource($order),
        ], 201);
    }
}
