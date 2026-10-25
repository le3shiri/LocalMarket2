@extends('layouts.staff')

@section('title', 'Detalle de Pedido ' . $order->order_number)

@section('content')
<div class="content-header">
    <div>
        <a href="{{ route('staff.orders.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.5rem; display: inline-block;">&larr; Volver a pedidos</a>
        <h1 class="page-title">Pedido {{ $order->order_number }}</h1>
        <p class="page-subtitle">Realizado el {{ $order->created_at->format('d/m/Y \a \l\a\s H:i') }}</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Artículos del Pedido</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="text-align: right;">Cantidad</th>
                            <th style="text-align: right;">Precio Unit.</th>
                            <th style="text-align: right;">IVA %</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name }}</strong>
                                    <div style="font-size: 0.8rem; color: #64748b; font-family: monospace;">SKU: {{ $item->product_sku }}</div>
                                </td>
                                <td style="text-align: right;">
                                    <strong>{{ $item->quantity_formatted }}</strong>
                                </td>
                                <td style="text-align: right;">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                                <td style="text-align: right;">{{ number_format($item->tax_rate, 0) }}%</td>
                                <td style="text-align: right;"><strong>{{ number_format($item->line_total, 2, ',', '.') }} €</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem; border-top: 1px solid #e2e8f0; padding-top: 1rem; max-width: 320px; margin-left: auto;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem; color: #475569;">
                    <span>Subtotal:</span>
                    <span>{{ number_format($order->subtotal, 2, ',', '.') }} €</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem; color: #475569;">
                    <span>IVA Incluido:</span>
                    <span>{{ number_format($order->tax_amount, 2, ',', '.') }} €</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem; color: #475569;">
                    <span>Coste de entrega:</span>
                    <span>{{ number_format($order->delivery_fee, 2, ',', '.') }} €</span>
                </div>
                @if($order->discount_amount > 0)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem; color: #166534;">
                        <span>Descuento aplicado:</span>
                        <span>-{{ number_format($order->discount_amount, 2, ',', '.') }} €</span>
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 2px solid #0f172a; font-weight: 800; font-size: 1.2rem; color: #0f172a;">
                    <span>Total:</span>
                    <span>{{ number_format($order->total_amount, 2, ',', '.') }} €</span>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Historial de Estados y Auditoría</h2>
            @if($order->statusHistories->isEmpty())
                <p style="color: #64748b;">No hay cambios de estado registrados para este pedido.</p>
            @else
                <div style="position: relative; padding-left: 1.5rem; border-left: 2px solid #e2e8f0;">
                    @foreach($order->statusHistories as $history)
                        <div style="margin-bottom: 1.25rem; position: relative;">
                            <div style="position: absolute; left: -1.95rem; top: 0.25rem; width: 10px; height: 10px; border-radius: 50%; background-color: #0284c7;"></div>
                            <div style="font-size: 0.85rem; color: #64748b;">{{ $history->created_at->format('d/m/Y H:i') }} &bull; Por: {{ $history->user?->name ?? 'Sistema' }}</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 0.25rem;">
                                {{ ucfirst(str_replace('_', ' ', $history->from_status ?? 'Nuevo')) }} &rarr; 
                                <span style="color: #0284c7;">{{ ucfirst(str_replace('_', ' ', $history->to_status)) }}</span>
                            </div>
                            @if($history->notes)
                                <div style="font-size: 0.9rem; color: #475569; margin-top: 0.25rem; background: #f8fafc; padding: 0.5rem; border-radius: 4px;">
                                    {{ $history->notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Actualizar Estado</h2>
            
            <div style="margin-bottom: 1rem;">
                <label class="form-label">Estado Actual</label>
                @php
                    $badgeColors = [
                        'pending_payment' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                        'paid' => ['bg' => '#dcfce7', 'text' => '#166534'],
                        'preparing' => ['bg' => '#e0e7ff', 'text' => '#3730a3'],
                        'ready_for_pickup' => ['bg' => '#fef08a', 'text' => '#854d0e'],
                        'out_for_delivery' => ['bg' => '#fed7aa', 'text' => '#9a3412'],
                        'completed' => ['bg' => '#bbf7d0', 'text' => '#14532d'],
                        'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                    ];
                    $c = $badgeColors[$order->status] ?? ['bg' => '#f1f5f9', 'text' => '#334155'];
                @endphp
                <div>
                    <span class="badge" style="font-size: 0.95rem; padding: 0.4rem 0.8rem; background-color: {{ $c['bg'] }}; color: {{ $c['text'] }};">
                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
            </div>

            @php
                $allowedNext = \App\Models\Order::ALLOWED_TRANSITIONS[$order->status] ?? [];
                if ($order->isPickup()) {
                    $allowedNext = array_values(array_diff($allowedNext, [\App\Models\Order::STATUS_OUT_FOR_DELIVERY]));
                } else {
                    $allowedNext = array_values(array_diff($allowedNext, [\App\Models\Order::STATUS_READY_FOR_PICKUP]));
                }
            @endphp

            @if(empty($allowedNext))
                <div style="background-color: #f1f5f9; padding: 0.75rem; border-radius: 6px; font-size: 0.9rem; color: #64748b;">
                    Este pedido se encuentra en un estado terminal y no admite transiciones manuales adicionales.
                </div>
            @else
                <form method="POST" action="{{ route('staff.orders.status', $order) }}">
                    @csrf
                    <div class="form-group">
                        <label for="status" class="form-label">Nuevo Estado</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="">-- Seleccionar nuevo estado --</option>
                            @foreach($allowedNext as $target)
                                <option value="{{ $target }}">
                                    {{ ucfirst(str_replace('_', ' ', $target)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">Notas del cambio</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Motivo o detalle de la actualización..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Actualizar Estado</button>
                </form>
            @endif
        </div>

        <div class="card">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Detalles del Cliente y Entrega</h2>
            <div style="font-size: 0.95rem; color: #334155; line-height: 1.6;">
                <p><strong>Cliente:</strong> {{ $order->customer_name }}</p>
                <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                <p><strong>Teléfono:</strong> {{ $order->customer_phone ?? 'No indicado' }}</p>
                
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 1rem 0;">

                <p><strong>Método:</strong> 
                    @if($order->isDelivery())
                        <span style="color: #3730a3; font-weight: 600;">Reparto a domicilio</span>
                    @else
                        <span style="color: #92400e; font-weight: 600;">Recogida en tienda</span>
                    @endif
                </p>

                @if($order->isDelivery())
                    <p><strong>Dirección:</strong> {{ $order->delivery_address }}</p>
                    <p><strong>Código Postal:</strong> {{ $order->delivery_postcode }} (Barcelona)</p>
                @endif

                @if($order->customer_notes)
                    <div style="margin-top: 1rem; background-color: #f8fafc; padding: 0.75rem; border-radius: 6px; border-left: 3px solid #0284c7;">
                        <strong style="display: block; margin-bottom: 0.25rem;">Notas del Cliente:</strong>
                        <span style="font-size: 0.9rem; color: #475569;">{{ $order->customer_notes }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
