@extends('layouts.app', ['title' => 'Mis Pedidos'])

@section('content')
<div class="container page">
    <div class="page-head">
        <h1>Mis Pedidos</h1>
        <a href="{{ route('catalog.index') }}" class="btn btn-sm btn-outline">+ Nueva compra</a>
    </div>

    @if($orders->isEmpty())
        <div class="card empty">
            <div class="empty-icon">📦</div>
            <h3>Aún no has realizado pedidos</h3>
            <p class="muted">Tus pedidos y su estado de preparación aparecerán aquí.</p>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary" style="margin-top: 1rem;">Ir a la tienda</a>
        </div>
    @else
        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nº Pedido</th>
                            <th>Fecha</th>
                            <th>Entrega</th>
                            <th>Productos</th>
                            <th class="num">Total</th>
                            <th>Estado pedido</th>
                            <th>Pago</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td><strong>{{ $order->number }}</strong></td>
                                <td class="muted">{{ fdate($order->created_at) }}</td>
                                <td>{{ $order->fulfilmentLabel() }}</td>
                                <td>{{ $order->items_count }} artículos</td>
                                <td class="num"><strong>{{ eur($order->total) }}</strong></td>
                                <td><span class="badge {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span></td>
                                <td>
                                    <span class="badge {{ $order->isPaid() ? 'badge-green' : 'badge-amber' }}">
                                        {{ $order->paymentStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('account.orders.show', $order->id) }}" class="btn btn-sm btn-outline">Ver detalle</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
