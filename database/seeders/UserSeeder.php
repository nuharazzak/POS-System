<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed initial demo users.
     */
    public function run(): void
    {
        // Admin Account
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // Cashier Account
        User::firstOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name'     => 'Alex Rivera',
                'password' => Hash::make('password'),
                'role'     => 'cashier',
            ]
        );
    }
}
