<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_id',
        'quantity',
        'reserved',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'reserved' => 'decimal:3',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Available stock = quantity - reserved (BR-INV-04)
     */
    public function getAvailableAttribute(): float
    {
        return max(0.0, (float) $this->quantity - (float) $this->reserved);
    }
}
