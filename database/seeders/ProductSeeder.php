<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed initial catalog products.
     */
    public function run(): void
    {
        $bev = Category::where('name', 'Beverages & Coffee')->first()?->id ?? 1;
        $main = Category::where('name', 'Main Courses')->first()?->id ?? 2;
        $snack = Category::where('name', 'Snacks & Sides')->first()?->id ?? 3;
        $dessert = Category::where('name', 'Desserts & Pastries')->first()?->id ?? 4;
        $combo = Category::where('name', 'Combos & Meals')->first()?->id ?? 5;

        $products = [
            // Beverages
            [
                'category_id'    => $bev,
                'name'           => 'Iced Caramel Macchiato',
                'description'    => 'Rich espresso layered with vanilla syrup, milk, and sweet caramel drizzle.',
                'price'          => 4.95,
                'stock_quantity' => 45,
                'image'          => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],
            [
                'category_id'    => $bev,
                'name'           => 'Classic Cappuccino',
                'description'    => 'Double shot espresso balanced with equal parts steamed milk and thick foam.',
                'price'          => 4.25,
                'stock_quantity' => 50,
                'image'          => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],
            [
                'category_id'    => $bev,
                'name'           => 'Matcha Green Tea Latte',
                'description'    => 'Ceremonial grade Japanese Uji matcha whisked with velvety steamed oat milk.',
                'price'          => 5.25,
                'stock_quantity' => 30,
                'image'          => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],
            [
                'category_id'    => $bev,
                'name'           => 'Sparkling Passionfruit Lemonade',
                'description'    => 'Freshly squeezed lemon juice, sparkling mineral water, and tropical passionfruit puree.',
                'price'          => 4.50,
                'stock_quantity' => 4, // low stock test
                'image'          => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],

            // Main Courses
            [
                'category_id'    => $main,
                'name'           => 'Artisan Truffle Beef Burger',
                'description'    => 'Angus beef patty with melted aged cheddar, arugula, truffle aioli, on brioche bun.',
                'price'          => 12.99,
                'stock_quantity' => 25,
                'image'          => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],
            [
                'category_id'    => $main,
                'name'           => 'Creamy Fettuccine Carbonara',
                'description'    => 'Fresh fettuccine with crispy pancetta, organic egg yolk, pecorino romano, and black pepper.',
                'price'          => 11.50,
                'stock_quantity' => 20,
                'image'          => 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],
            [
                'category_id'    => $main,
                'name'           => 'Margherita Wood-Fired Pizza',
                'description'    => 'San Marzano tomato base, fresh buffalo mozzarella, fragrant sweet basil, and extra virgin olive oil.',
                'price'          => 13.50,
                'stock_quantity' => 15,
                'image'          => 'https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],
            [
                'category_id'    => $main,
                'name'           => 'Crispy Chicken Brioche Sandwich',
                'description'    => 'Buttermilk fried chicken breast, spicy coleslaw, house pickles, and honey-dijon spread.',
                'price'          => 10.95,
                'stock_quantity' => 18,
                'image'          => 'https://images.unsplash.com/photo-1625813506062-0aeb1d7a094b?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],

            // Snacks & Sides
            [
                'category_id'    => $snack,
                'name'           => 'Truffle Parmesan French Fries',
                'description'    => 'Double-crisped shoestring fries tossed in white truffle oil and freshly grated parmesan.',
                'price'          => 5.75,
                'stock_quantity' => 40,
                'image'          => 'https://images.unsplash.com/photo-1576107232684-1279f3908594?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],
            [
                'category_id'    => $snack,
                'name'           => 'Buffalo Chicken Wings (6 pcs)',
                'description'    => 'Glazed in tangy Louisiana buffalo sauce, served with celery sticks and blue cheese dip.',
                'price'          => 8.50,
                'stock_quantity' => 22,
                'image'          => 'https://images.unsplash.com/photo-1567620832903-9fc6debc209f?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],
            [
                'category_id'    => $snack,
                'name'           => 'Crispy Mozzarella Sticks',
                'description'    => 'Herb-seasoned crumbed mozzarella melted inside, served with warm marinara dipping sauce.',
                'price'          => 6.25,
                'stock_quantity' => 0, // out of stock test
                'image'          => 'https://images.unsplash.com/photo-1531749668029-2db88e4276c7?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],

            // Desserts
            [
                'category_id'    => $dessert,
                'name'           => 'New York Classic Cheesecake',
                'description'    => 'Rich and creamy baked cheesecake on a golden graham cracker crust with strawberry coulis.',
                'price'          => 6.50,
                'stock_quantity' => 14,
                'image'          => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],
            [
                'category_id'    => $dessert,
                'name'           => 'Warm Lava Chocolate Cake',
                'description'    => 'Decadent Belgian dark chocolate cake with a molten center, served with vanilla bean ice cream.',
                'price'          => 7.25,
                'stock_quantity' => 12,
                'image'          => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],

            // Combos
            [
                'category_id'    => $combo,
                'name'           => 'Burger, Truffle Fries & Drink Combo',
                'description'    => 'Choice of Truffle Burger, Truffle Fries, and any Specialty Iced Beverage.',
                'price'          => 18.95,
                'stock_quantity' => 20,
                'image'          => 'https://images.unsplash.com/photo-1594212699903-ec8a3eca50f5?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ],
            [
                'category_id'    => $combo,
                'name'           => 'Breakfast Croissant & Latte Special',
                'description'    => 'Freshly baked butter croissant served with a hot cappuccino or latte.',
                'price'          => 7.95,
                'stock_quantity' => 35,
                'image'          => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=500&auto=format&fit=crop&q=60',
                'is_active'      => true,
            ]
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(['name' => $p['name']], $p);
        }
    }
}
