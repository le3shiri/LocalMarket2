<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public const UPDATED_AT = null; // Immutable ledger: cannot be edited or deleted

    public const TYPE_RECEIPT = 'receipt';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_STORE_SALE = 'store_sale';

    public const TYPE_RESERVATION = 'reservation';

    public const TYPE_RELEASE = 'release';

    public const TYPE_SALE = 'sale';

    public const TYPE_RETURN = 'return';

    public const TYPES = [
        self::TYPE_RECEIPT => 'Entrada proveedor',
        self::TYPE_TRANSFER_OUT => 'Salida traspaso',
        self::TYPE_TRANSFER_IN => 'Entrada traspaso',
        self::TYPE_ADJUSTMENT => 'Ajuste manual',
        self::TYPE_STORE_SALE => 'Venta en tienda (caja)',
        self::TYPE_RESERVATION => 'Reserva pedido online',
        self::TYPE_RELEASE => 'Liberación reserva',
        self::TYPE_SALE => 'Consumo venta online',
        self::TYPE_RETURN => 'Devolución de stock',
    ];

    protected $fillable = [
        'product_id',
        'location_id',
        'user_id',
        'order_id',
        'type',
        'quantity',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'created_at' => 'datetime',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
