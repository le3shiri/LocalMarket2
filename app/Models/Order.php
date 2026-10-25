<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Statuses
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';

    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    // Payment statuses
    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_REFUNDED = 'refunded';

    // Fulfilment methods
    public const FULFILMENT_DELIVERY = 'delivery';

    public const FULFILMENT_PICKUP = 'pickup';

    public const STATUS_LABELS = [
        self::STATUS_PENDING_PAYMENT => 'Pendiente de pago',
        self::STATUS_CONFIRMED => 'Confirmado',
        self::STATUS_PREPARING => 'En preparación',
        self::STATUS_READY_FOR_PICKUP => 'Listo para recoger',
        self::STATUS_OUT_FOR_DELIVERY => 'En reparto',
        self::STATUS_COMPLETED => 'Entregado / Completado',
        self::STATUS_CANCELLED => 'Cancelado',
    ];

    public const STATUS_BADGES = [
        self::STATUS_PENDING_PAYMENT => 'badge-amber',
        self::STATUS_CONFIRMED => 'badge-blue',
        self::STATUS_PREPARING => 'badge-violet',
        self::STATUS_READY_FOR_PICKUP => 'badge-green',
        self::STATUS_OUT_FOR_DELIVERY => 'badge-green',
        self::STATUS_COMPLETED => 'badge-green',
        self::STATUS_CANCELLED => 'badge-red',
    ];

    public const PAYMENT_STATUS_LABELS = [
        self::PAYMENT_PENDING => 'Pendiente',
        self::PAYMENT_PAID => 'Pagado',
        self::PAYMENT_FAILED => 'Fallido',
        self::PAYMENT_REFUNDED => 'Reembolsado',
    ];

    public const FULFILMENT_LABELS = [
        self::FULFILMENT_DELIVERY => 'Envío a domicilio',
        self::FULFILMENT_PICKUP => 'Recogida en tienda',
    ];

    /**
     * Allowed status transitions per BR-ORD-04.
     */
    public const TRANSITIONS = [
        self::STATUS_PENDING_PAYMENT => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PREPARING, self::STATUS_CANCELLED],
        self::STATUS_PREPARING => [self::STATUS_READY_FOR_PICKUP, self::STATUS_OUT_FOR_DELIVERY, self::STATUS_CANCELLED],
        self::STATUS_READY_FOR_PICKUP => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [], // final state
        self::STATUS_CANCELLED => [], // final state
    ];

    protected $fillable = [
        'number',
        'user_id',
        'status',
        'payment_status',
        'fulfilment_method',
        'subtotal',
        'discount',
        'delivery_fee',
        'total',
        'coupon_id',
        'coupon_code',
        'customer_name',
        'customer_email',
        'phone',
        'address',
        'postcode',
        'city',
        'notes',
        'paid_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge';
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? $this->payment_status;
    }

    public function fulfilmentLabel(): string
    {
        return self::FULFILMENT_LABELS[$this->fulfilment_method] ?? $this->fulfilment_method;
    }

    public function isDelivery(): bool
    {
        return $this->fulfilment_method === self::FULFILMENT_DELIVERY;
    }

    public function isPickup(): bool
    {
        return $this->fulfilment_method === self::FULFILMENT_PICKUP;
    }
}
