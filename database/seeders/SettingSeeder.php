<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed initial store settings.
     */
    public function run(): void
    {
        Setting::firstOrCreate(
            ['id' => 1],
            [
                'store_name'          => 'The Artisanal Kitchen & Cafe',
                'address'             => '742 Evergreen Terrace, Sector 4',
                'phone'               => '+1 (555) 234-5678',
                'currency'            => '$',
                'tax_rate'            => 10.00,
                'low_stock_threshold' => 5,
            ]
        );
    }
}
