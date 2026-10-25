@extends('layouts.app', ['title' => 'Pedido ' . $order->number])

@section('content')
<div class="container page">
    <div class="breadcrumbs">
        <a href="{{ route('home') }}">Inicio</a> &rsaquo;
        <a href="{{ route('account.orders') }}">Mis pedidos</a> &rsaquo;
        <span>{{ $order->number }}</span>
    </div>

    <div class="page-head">
        <div>
            <h1>Pedido {{ $order->number }}</h1>
            <p class="muted">Realizado el {{ fdate($order->created_at) }}</p>
        </div>
        <div class="flex">
            <span class="badge {{ $order->statusBadge() }}" style="font-size: .95rem; padding: .4rem .9rem;">
                {{ $order->statusLabel() }}
            </span>
            <span class="badge {{ $order->isPaid() ? 'badge-green' : 'badge-amber' }}" style="font-size: .95rem; padding: .4rem .9rem;">
                Pago: {{ $order->paymentStatusLabel() }}
            </span>
        </div>
    </div>

    @if($order->isPendingPayment())
        <div class="alert alert-warning" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span style="font-size: 1.5rem;">ℹ️</span>
                <div>
                    <strong>Stock reservado temporalmente.</strong> Tu pedido está pendiente de confirmación de pago.
                </div>
            </div>
            <a href="{{ route('payment.show', $order->id) }}" class="btn btn-primary" style="margin-left: auto;">
                💳 Proceder al Pago Ahora ({{ eur($order->total) }})
            </a>
        </div>
    @endif

    <div class="two-col">
        <div>
            <!-- Order Items -->
            <div class="card">
                <h3>Productos en el pedido</h3>
                <div class="table-wrap" style="border: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="num">Precio unit.</th>
                                <th class="num">Cantidad</th>
                                <th class="num">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td><strong>{{ $item->product_name }}</strong></td>
                                    <td class="num">{{ eur($item->unit_price) }}</td>
                                    <td class="num">{{ qty($item->quantity, $item->sale_unit) }}</td>
                                    <td class="num"><strong>{{ eur($item->line_total) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="card">
                <h3>Seguimiento del pedido</h3>
                <ul class="timeline" style="margin-top: 1rem;">
                    @foreach($order->statusHistories as $history)
                        <li>
                            <strong>{{ \App\Models\Order::STATUS_LABELS[$history->to_status] ?? $history->to_status }}</strong>
                            <small>{{ fdate($history->created_at) }} · {{ $history->note ?: 'Actualización de estado' }}</small>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div>
            <!-- Summary and Details -->
            <div class="card summary">
                <h3>Detalle económico</h3>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong>{{ eur($order->subtotal) }}</strong>
                </div>
                @if($order->discount > 0)
                    <div class="summary-row">
                        <span>Descuento cupón:</span>
                        <strong class="discount">-{{ eur($order->discount) }}</strong>
                    </div>
                @endif
                <div class="summary-row">
                    <span>Envío:</span>
                    <span>{{ $order->delivery_fee > 0 ? eur($order->delivery_fee) : 'Gratis' }}</span>
                </div>
                <div class="summary-row total">
                    <span>Total pedido:</span>
                    <span>{{ eur($order->total) }}</span>
                </div>

                <hr style="margin: 1.2rem 0; border: 0; border-top: 1px solid var(--border);">

                <h4>Datos de {{ $order->fulfilmentLabel() }}</h4>
                <p style="font-size: .88rem; margin-bottom: .4rem;"><strong>Destinatario:</strong> {{ $order->customer_name }}</p>
                <p style="font-size: .88rem; margin-bottom: .4rem;"><strong>Teléfono:</strong> {{ $order->phone }}</p>

                @if($order->isDelivery())
                    <p style="font-size: .88rem; margin-bottom: .4rem;">
                        <strong>Dirección:</strong> {{ $order->address }}<br>
                        {{ $order->postcode }} {{ $order->city }}
                    </p>
                @else
                    <p style="font-size: .88rem; margin-bottom: .4rem;">
                        <strong>Punto de recogida:</strong> Carrer del Mercat, 12, Ciutat Vella, 08001 Barcelona
                    </p>
                @endif

                @if($order->notes)
                    <p style="font-size: .85rem; color: var(--muted); margin-top: .6rem;">
                        <strong>Notas:</strong> {{ $order->notes }}
                    </p>
                @endif

                @if($order->isPendingPayment())
                    <div style="margin-top: 1.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                        <form action="{{ route('account.orders.cancel', $order->id) }}" method="POST" data-confirm="¿Seguro que deseas cancelar este pedido? Se liberará el stock reservado.">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-ghost btn-block" style="color: var(--red-600);">
                                Cancelar pedido pendiente
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
