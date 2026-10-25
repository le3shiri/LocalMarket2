@extends('layouts.staff', ['title' => 'Panel de Control'])

@section('content')
<div class="stats">
    <div class="stat">
        <div class="label">Catálogo Activo</div>
        <div class="value">{{ $totalProducts }}</div>
        <span class="ico">🍎</span>
    </div>
    <div class="stat">
        <div class="label">Ubicaciones</div>
        <div class="value">{{ $totalLocations }}</div>
        <span class="ico">🏬</span>
    </div>
    <div class="stat">
        <div class="label">Alertas Stock Bajo</div>
        <div class="value" style="color: {{ count($lowStockItems) > 0 ? 'var(--orange-600)' : 'var(--green-700)' }};">
            {{ count($lowStockItems) }}
        </div>
        <span class="ico">⚠️</span>
    </div>
    <div class="stat">
        <div class="label">Lotes Próx. Caducidad</div>
        <div class="value" style="color: {{ count($nearExpiryBatches) > 0 ? 'var(--red-600)' : 'var(--green-700)' }};">
            {{ count($nearExpiryBatches) }}
        </div>
        <span class="ico">⏰</span>
    </div>
</div>

<div class="grid-2">
    <!-- Low Stock Alert Box -->
    <div class="card">
        <div class="card-title">
            <h3>⚠️ Alertas de Stock Bajo (Online: {{ $onlineLocation->name ?? 'Barcelona' }})</h3>
            <a href="{{ route('staff.inventory.index') }}" class="btn btn-sm btn-ghost">Ver todo</a>
        </div>
        @if(empty($lowStockItems) || count($lowStockItems) === 0)
            <div class="empty" style="padding: 1.5rem;">
                <p>✓ Todas las existencias están por encima del umbral mínimo.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="num">Disponible</th>
                            <th class="num">Umbral</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockItems as $item)
                            <tr>
                                <td><strong>{{ $item->product->name }}</strong></td>
                                <td class="num"><strong class="qty-negative">{{ qty($item->available, $item->product->sale_unit) }}</strong></td>
                                <td class="num muted">{{ qty($item->product->low_stock_threshold, $item->product->sale_unit) }}</td>
                                <td>
                                    @if(auth()->user()->hasRole('admin', 'warehouse'))
                                        <a href="{{ route('staff.inventory.transfer') }}" class="btn btn-sm btn-outline">Reponer</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Near Expiry Alert Box -->
    <div class="card">
        <div class="card-title">
            <h3>⏰ Lotes Próximos a Caducar (&le; 3 días)</h3>
            @if(auth()->user()->hasRole('admin', 'warehouse'))
                <a href="{{ route('staff.batches.index') }}" class="btn btn-sm btn-ghost">Ver lotes</a>
            @endif
        </div>
        @if(empty($nearExpiryBatches) || count($nearExpiryBatches) === 0)
            <div class="empty" style="padding: 1.5rem;">
                <p>✓ No hay lotes próximos a caducar en los próximos 3 días.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Ubicación</th>
                            <th>Caducidad</th>
                            <th class="num">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nearExpiryBatches as $batch)
                            <tr>
                                <td><strong>{{ $batch->product->name }}</strong></td>
                                <td>{{ $batch->location->name }}</td>
                                <td>
                                    <span class="badge {{ $batch->isExpired() ? 'badge-red' : 'badge-amber' }}">
                                        {{ fdate($batch->expiry_date, false) }}
                                    </span>
                                </td>
                                <td class="num"><strong>{{ qty($batch->quantity, $batch->product->sale_unit) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
