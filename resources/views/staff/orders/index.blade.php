@extends('layouts.staff')

@section('title', 'Gestión de Pedidos')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Pedidos de Clientes</h1>
        <p class="page-subtitle">Supervisión, preparación y actualización de pedidos online y recogida en tienda</p>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <form method="GET" action="{{ route('staff.orders.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 220px; margin-bottom: 0;">
            <label for="search" class="form-label">Buscar Pedido / Cliente</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Núm. pedido, nombre o email...">
        </div>

        <div class="form-group" style="min-width: 180px; margin-bottom: 0;">
            <label for="status" class="form-label">Estado</label>
            <select name="status" id="status" class="form-control">
                <option value="">-- Todos los estados --</option>
                @foreach(\App\Models\Order::STATUSES as $statusKey)
                    <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $statusKey)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="min-width: 180px; margin-bottom: 0;">
            <label for="fulfilment_type" class="form-label">Método</label>
            <select name="fulfilment_type" id="fulfilment_type" class="form-control">
                <option value="">-- Todos los métodos --</option>
                <option value="delivery" {{ request('fulfilment_type') === 'delivery' ? 'selected' : '' }}>A domicilio (08001-08042)</option>
                <option value="pickup" {{ request('fulfilment_type') === 'pickup' ? 'selected' : '' }}>Recogida en tienda</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('staff.orders.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

<div class="card">
    @if($orders->isEmpty())
        <div style="text-align: center; padding: 3rem 1rem;">
            <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 1rem;">No se encontraron pedidos con los filtros seleccionados.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Método</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>
                                <strong style="font-family: monospace;">{{ $order->order_number }}</strong>
                            </td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div><strong>{{ $order->customer_name }}</strong></div>
                                <small style="color: #64748b;">{{ $order->customer_email }}</small>
                            </td>
                            <td>
                                @if($order->isDelivery())
                                    <span class="badge" style="background-color: #e0e7ff; color: #3730a3;">Reparto a domicilio</span>
                                @else
                                    <span class="badge" style="background-color: #fef3c7; color: #92400e;">Recogida en tienda</span>
                                @endif
                            </td>
                            <td><strong>{{ number_format($order->total_amount, 2, ',', '.') }} €</strong></td>
                            <td>
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
                                    $col = $badgeColors[$order->status] ?? ['bg' => '#f1f5f9', 'text' => '#334155'];
                                @endphp
                                <span class="badge" style="background-color: {{ $col['bg'] }}; color: {{ $col['text'] }};">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('staff.orders.show', $order) }}" class="btn btn-secondary btn-sm">Ver Detalle</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
