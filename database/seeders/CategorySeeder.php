<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed standard restaurant/cafe categories.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Beverages & Coffee', 'description' => 'Espresso drinks, specialty teas, cold brews, and fresh juices'],
            ['name' => 'Main Courses', 'description' => 'Signature pasta, gourmet burgers, wood-fired pizzas, and steaks'],
            ['name' => 'Snacks & Sides', 'description' => 'Truffle fries, garlic bread, chicken wings, and onion rings'],
            ['name' => 'Desserts & Pastries', 'description' => 'Freshly baked croissants, cheesecakes, brownies, and gelato'],
            ['name' => 'Combos & Meals', 'description' => 'Value lunch combos, breakfast deals, and family platters'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
