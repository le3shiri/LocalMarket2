@extends('layouts.staff')

@section('title', 'Detalle de Pedido ' . $order->number)

@section('content')
<div class="content-header">
    <div>
        <a href="{{ route('staff.orders.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.5rem; display: inline-block;">&larr; Volver a pedidos</a>
        <h1 class="page-title">Pedido {{ $order->number }}</h1>
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
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name }}</strong>
                                    @if($item->product)
                                        <div style="font-size: 0.8rem; color: #64748b;">Ref: #{{ $item->product_id }}</div>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <strong>{{ $item->quantity_formatted }}</strong>
                                </td>
                                <td style="text-align: right;">{{ eur($item->unit_price) }}</td>
                                <td style="text-align: right;"><strong>{{ eur($item->line_total) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem; border-top: 1px solid #e2e8f0; padding-top: 1rem; max-width: 320px; margin-left: auto;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem; color: #475569;">
                    <span>Subtotal:</span>
                    <span>{{ eur($order->subtotal) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem; color: #475569;">
                    <span>Coste de entrega:</span>
                    <span>{{ eur($order->delivery_fee) }}</span>
                </div>
                @if($order->discount > 0)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.95rem; color: #166534;">
                        <span>Descuento aplicado:</span>
                        <span>-{{ eur($order->discount) }}</span>
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 2px solid #0f172a; font-weight: 800; font-size: 1.2rem; color: #0f172a;">
                    <span>Total:</span>
                    <span>{{ eur($order->total) }}</span>
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
                                {{ \App\Models\Order::STATUS_LABELS[$history->from_status] ?? ucfirst(str_replace('_', ' ', $history->from_status ?? 'Nuevo')) }} &rarr; 
                                <span style="color: #0284c7;">{{ \App\Models\Order::STATUS_LABELS[$history->to_status] ?? ucfirst(str_replace('_', ' ', $history->to_status)) }}</span>
                            </div>
                            @if($history->note)
                                <div style="font-size: 0.9rem; color: #475569; margin-top: 0.25rem; background: #f8fafc; padding: 0.5rem; border-radius: 4px;">
                                    {{ $history->note }}
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
                <div>
                    <span class="badge {{ $order->statusBadge() }}" style="font-size: 0.95rem; padding: 0.4rem 0.8rem;">
                        {{ $order->statusLabel() }}
                    </span>
                </div>
            </div>

            @php
                $allowedNext = \App\Models\Order::TRANSITIONS[$order->status] ?? [];
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
                                    {{ \App\Models\Order::STATUS_LABELS[$target] ?? ucfirst(str_replace('_', ' ', $target)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="note" class="form-label">Notas del cambio</label>
                        <textarea name="note" id="note" class="form-control" rows="3" placeholder="Motivo o detalle de la actualización..."></textarea>
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
                <p><strong>Teléfono:</strong> {{ $order->phone ?? 'No indicado' }}</p>
                
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 1rem 0;">

                <p><strong>Método:</strong> 
                    @if($order->isDelivery())
                        <span style="color: #3730a3; font-weight: 600;">Reparto a domicilio</span>
                    @else
                        <span style="color: #92400e; font-weight: 600;">Recogida en tienda</span>
                    @endif
                </p>

                @if($order->isDelivery())
                    <p><strong>Dirección:</strong> {{ $order->address }}</p>
                    <p><strong>Código Postal:</strong> {{ $order->postcode }} (Barcelona)</p>
                @endif

                @if($order->notes)
                    <div style="margin-top: 1rem; background-color: #f8fafc; padding: 0.75rem; border-radius: 6px; border-left: 3px solid #0284c7;">
                        <strong style="display: block; margin-bottom: 0.25rem;">Notas del Cliente:</strong>
                        <span style="font-size: 0.9rem; color: #475569;">{{ $order->notes }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
