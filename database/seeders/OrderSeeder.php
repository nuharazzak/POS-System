<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Seed realistic sample orders for dashboard/reports demonstration.
     */
    public function run(): void
    {
        $cashier = User::where('role', 'cashier')->first() ?? User::first();
        if (!$cashier) return;

        $burger = Product::where('name', 'Artisan Truffle Beef Burger')->first();
        $macchiato = Product::where('name', 'Iced Caramel Macchiato')->first();
        $fries = Product::where('name', 'Truffle Parmesan French Fries')->first();
        $cheesecake = Product::where('name', 'New York Classic Cheesecake')->first();

        if (!$burger || !$macchiato) return;

        // Sample Order 1: Cash payment
        $order1 = Order::firstOrCreate(
            ['order_number' => 'ORD-000001'],
            [
                'user_id'         => $cashier->id,
                'subtotal'        => 23.69,
                'discount_type'   => 'percentage',
                'discount_value'  => 0.00,
                'discount_amount' => 0.00,
                'tax_rate'        => 10.00,
                'tax_amount'      => 2.37,
                'total_amount'    => 26.06,
                'payment_method'  => 'cash',
                'amount_received' => 30.00,
                'change_amount'   => 3.94,
                'status'          => 'completed',
                'created_at'      => Carbon::now()->subHours(2),
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order1->id, 'product_id' => $burger->id],
            ['quantity' => 1, 'unit_price' => 12.99, 'total_price' => 12.99]
        );
        OrderItem::firstOrCreate(
            ['order_id' => $order1->id, 'product_id' => $fries->id ?? $burger->id],
            ['quantity' => 1, 'unit_price' => 5.75, 'total_price' => 5.75]
        );
        OrderItem::firstOrCreate(
            ['order_id' => $order1->id, 'product_id' => $macchiato->id],
            ['quantity' => 1, 'unit_price' => 4.95, 'total_price' => 4.95]
        );

        // Sample Order 2: Card payment with discount
        $order2 = Order::firstOrCreate(
            ['order_number' => 'ORD-000002'],
            [
                'user_id'         => $cashier->id,
                'subtotal'        => 20.70,
                'discount_type'   => 'percentage',
                'discount_value'  => 10.00,
                'discount_amount' => 2.07,
                'tax_rate'        => 10.00,
                'tax_amount'      => 1.86,
                'total_amount'    => 20.49,
                'payment_method'  => 'card',
                'amount_received' => 20.49,
                'change_amount'   => 0.00,
                'status'          => 'completed',
                'created_at'      => Carbon::now()->subMinutes(45),
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order2->id, 'product_id' => $burger->id],
            ['quantity' => 1, 'unit_price' => 12.99, 'total_price' => 12.99]
        );
        OrderItem::firstOrCreate(
            ['order_id' => $order2->id, 'product_id' => $cheesecake->id ?? $macchiato->id],
            ['quantity' => 1, 'unit_price' => 6.50, 'total_price' => 6.50]
        );
    }
}
