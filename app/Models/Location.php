<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    public const TYPE_WAREHOUSE = 'warehouse';

    public const TYPE_STORE = 'store';

    protected $fillable = [
        'name',
        'type',
        'address',
        'is_online_source',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_online_source' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isStore(): bool
    {
        return $this->type === self::TYPE_STORE;
    }

    public function isWarehouse(): bool
    {
        return $this->type === self::TYPE_WAREHOUSE;
    }
}
