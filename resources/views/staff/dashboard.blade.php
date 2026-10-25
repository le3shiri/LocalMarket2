@extends('layouts.staff', ['title' => 'Panel de Control'])

@section('content')
<div class="stats">
    <div class="stat stat-primary">
        <div class="stat-header">
            <span class="label">Catálogo Activo</span>
            <div class="stat-icon-wrap">
                <x-icon name="shopping-bag" size="18" />
            </div>
        </div>
        <div class="value">{{ $totalProducts }}</div>
        <div class="stat-footer">
            <span class="badge badge-green">Activos en catálogo</span>
        </div>
    </div>

    <div class="stat stat-blue">
        <div class="stat-header">
            <span class="label">Ubicaciones</span>
            <div class="stat-icon-wrap">
                <x-icon name="warehouse" size="18" />
            </div>
        </div>
        <div class="value">{{ $totalLocations }}</div>
        <div class="stat-footer">
            <span>Almacenes y tiendas físicas</span>
        </div>
    </div>

    <div class="stat {{ count($lowStockItems) > 0 ? 'stat-amber' : 'stat-primary' }}">
        <div class="stat-header">
            <span class="label">Alertas Stock Bajo</span>
            <div class="stat-icon-wrap">
                <x-icon name="alert-triangle" size="18" />
            </div>
        </div>
        <div class="value" style="color: {{ count($lowStockItems) > 0 ? 'var(--amber-600)' : 'var(--emerald-700)' }};">
            {{ count($lowStockItems) }}
        </div>
        <div class="stat-footer">
            @if(count($lowStockItems) > 0)
                <span class="badge badge-amber">Requieren reposición</span>
            @else
                <span class="badge badge-green">Stock en rango óptimo</span>
            @endif
        </div>
    </div>

    <div class="stat {{ count($nearExpiryBatches) > 0 ? 'stat-red' : 'stat-primary' }}">
        <div class="stat-header">
            <span class="label">Lotes Próx. Caducidad</span>
            <div class="stat-icon-wrap">
                <x-icon name="clock" size="18" />
            </div>
        </div>
        <div class="value" style="color: {{ count($nearExpiryBatches) > 0 ? 'var(--red-600)' : 'var(--emerald-700)' }};">
            {{ count($nearExpiryBatches) }}
        </div>
        <div class="stat-footer">
            @if(count($nearExpiryBatches) > 0)
                <span class="badge badge-red">&le; 3 días restantes</span>
            @else
                <span class="badge badge-green">Sin caducidades urgentes</span>
            @endif
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Low Stock Alert Box -->
    <div class="card">
        <div class="card-title">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <x-icon name="alert-triangle" size="18" class="text-amber" />
                <h3 style="font-size: 1.05rem;">Stock Bajo (Online: {{ $onlineLocation->name ?? 'Barcelona' }})</h3>
            </div>
            <a href="{{ route('staff.inventory.index') }}" class="btn btn-sm btn-ghost">Ver inventario &rarr;</a>
        </div>
        @if(empty($lowStockItems) || count($lowStockItems) === 0)
            <div class="empty" style="padding: 2rem 1rem;">
                <div class="empty-icon" style="background: var(--emerald-50); color: var(--emerald-700);">
                    <x-icon name="check" size="24" stroke="2.5" />
                </div>
                <h3>Existencias en estado óptimo</h3>
                <p>Todos los productos del centro logístico están por encima de su umbral mínimo de seguridad.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="num">Disponible</th>
                            <th class="num">Umbral</th>
                            <th style="width: 100px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product->name }}</strong>
                                    <div class="muted" style="font-size: 0.78rem;">Ref: #{{ $item->product_id }}</div>
                                </td>
                                <td class="num"><strong style="color: var(--red-600);">{{ qty($item->available, $item->product->sale_unit) }}</strong></td>
                                <td class="num muted">{{ qty($item->product->low_stock_threshold, $item->product->sale_unit) }}</td>
                                <td>
                                    @if(auth()->user()->hasRole('admin', 'warehouse'))
                                        <a href="{{ route('staff.inventory.transfer') }}" class="btn btn-sm btn-outline" style="font-size: 0.78rem; padding: 0.25rem 0.6rem;">
                                            <x-icon name="refresh-cw" size="12" />
                                            <span>Reponer</span>
                                        </a>
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
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <x-icon name="clock" size="18" />
                <h3 style="font-size: 1.05rem;">Lotes Próximos a Caducar (&le; 3 días)</h3>
            </div>
            @if(auth()->user()->hasRole('admin', 'warehouse'))
                <a href="{{ route('staff.batches.index') }}" class="btn btn-sm btn-ghost">Ver lotes &rarr;</a>
            @endif
        </div>
        @if(empty($nearExpiryBatches) || count($nearExpiryBatches) === 0)
            <div class="empty" style="padding: 2rem 1rem;">
                <div class="empty-icon" style="background: var(--emerald-50); color: var(--emerald-700);">
                    <x-icon name="check" size="24" stroke="2.5" />
                </div>
                <h3>Sin alertas de caducidad</h3>
                <p>No hay lotes con fecha de consumo preferente próxima en los próximos 3 días.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Producto / Lote</th>
                            <th>Ubicación</th>
                            <th>Caducidad</th>
                            <th class="num">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nearExpiryBatches as $batch)
                            <tr>
                                <td>
                                    <strong>{{ $batch->product->name }}</strong>
                                    <div class="muted" style="font-size: 0.78rem; font-family: monospace;">Lote: {{ $batch->lot_code }}</div>
                                </td>
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
