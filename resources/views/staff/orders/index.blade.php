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
            <label for="q" class="form-label">Buscar Pedido / Cliente</label>
            <input type="text" name="q" id="q" class="form-control" value="{{ request('q') }}" placeholder="Núm. pedido, nombre o email...">
        </div>

        <div class="form-group" style="min-width: 180px; margin-bottom: 0;">
            <label for="status" class="form-label">Estado</label>
            <select name="status" id="status" class="form-control">
                <option value="">-- Todos los estados --</option>
                @foreach(\App\Models\Order::STATUS_LABELS as $statusKey => $label)
                    <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="min-width: 180px; margin-bottom: 0;">
            <label for="fulfilment_method" class="form-label">Método</label>
            <select name="fulfilment_method" id="fulfilment_method" class="form-control">
                <option value="">-- Todos los métodos --</option>
                <option value="delivery" {{ request('fulfilment_method') === 'delivery' ? 'selected' : '' }}>A domicilio (08001-08042)</option>
                <option value="pickup" {{ request('fulfilment_method') === 'pickup' ? 'selected' : '' }}>Recogida en tienda</option>
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
                                <strong style="font-family: monospace;">{{ $order->number }}</strong>
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
                            <td><strong>{{ eur($order->total) }}</strong></td>
                            <td>
                                <span class="badge {{ $order->statusBadge() }}">
                                    {{ $order->statusLabel() }}
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
