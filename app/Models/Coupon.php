<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED = 'fixed';

    public const TYPES = [
        self::TYPE_PERCENTAGE => 'Porcentaje (%)',
        self::TYPE_FIXED => 'Importe fijo (€)',
    ];

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'uses_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_uses' => 'integer',
            'uses_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isValidForAmount(float $subtotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        if ($subtotal < (float) $this->min_order_amount) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (! $this->isValidForAmount($subtotal)) {
            return 0.00;
        }

        if ($this->type === self::TYPE_PERCENTAGE) {
            $discount = round($subtotal * ((float) $this->value / 100), 2);
        } else {
            $discount = (float) $this->value;
        }

        // Discount cannot exceed the subtotal
        return min($subtotal, max(0.00, $discount));
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function valueFormatted(): string
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return number_format($this->value, 0).'%';
        }

        return number_format($this->value, 2, ',', '.').' €';
    }
}
