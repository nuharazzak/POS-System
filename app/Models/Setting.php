<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'address',
        'phone',
        'currency',
        'tax_rate',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'low_stock_threshold' => 'integer',
        ];
    }

    /**
     * Singleton accessor for global settings.
     */
    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'store_name' => 'My Restaurant & Cafe POS',
                'address' => '45 Bistro Lane, Downtown',
                'phone' => '+1 (555) 839-2041',
                'currency' => '$',
                'tax_rate' => 10.00,
                'low_stock_threshold' => 5,
            ]
        );
    }
}
