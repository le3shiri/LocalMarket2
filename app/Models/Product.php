<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    public const UNIT_PIECE = 'unit';

    public const UNIT_KG = 'kg';

    public const UNIT_PACK = 'pack';

    public const UNITS = [
        self::UNIT_PIECE => 'Unidad (ud.)',
        self::UNIT_KG => 'Kilogramo (kg)',
        self::UNIT_PACK => 'Paquete (paq.)',
    ];

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image_path',
        'price',
        'sale_unit',
        'shelf_location',
        'tracks_expiry',
        'low_stock_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'low_stock_threshold' => 'decimal:3',
            'tracks_expiry' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unitLabel(): string
    {
        return self::UNITS[$this->sale_unit] ?? $this->sale_unit;
    }
}
