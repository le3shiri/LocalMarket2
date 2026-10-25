@extends('layouts.staff', ['title' => 'Historial de Movimientos de Stock'])

@section('content')
<div class="card">
    <div class="card-title">
        <h2>Histórico de Movimientos de Stock</h2>
        <a href="{{ route('staff.inventory.index') }}" class="btn btn-outline btn-sm">&larr; Volver a Inventario</a>
    </div>

    <form method="GET" action="{{ route('staff.inventory.movements') }}" class="filters">
        <div class="field">
            <label for="location_id">Ubicación</label>
            <select id="location_id" name="location_id">
                <option value="">Todas</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                        {{ $loc->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="product_id">Producto</label>
            <select id="product_id" name="product_id">
                <option value="">Todos</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="type">Tipo</label>
            <select id="type" name="type">
                <option value="">Todos los tipos</option>
                @foreach(\App\Models\StockMovement::TYPES as $key => $lbl)
                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                        {{ $lbl }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-outline btn-sm" style="margin-bottom: 2px;">Filtrar</button>
        @if(request()->hasAny(['location_id', 'product_id', 'type']))
            <a href="{{ route('staff.inventory.movements') }}" class="btn btn-ghost btn-sm" style="margin-bottom: 2px;">Limpiar</a>
        @endif
    </form>

    @if($movements->isEmpty())
        <div class="empty">
            <p>No se encontraron movimientos registrados.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Ubicación</th>
                        <th>Tipo</th>
                        <th class="num">Cantidad</th>
                        <th>Usuario</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $m)
                        <tr>
                            <td class="muted" style="white-space: nowrap;">{{ fdate($m->created_at) }}</td>
                            <td><strong>{{ $m->product->name }}</strong></td>
                            <td>{{ $m->location->name }}</td>
                            <td><span class="badge">{{ $m->typeLabel() }}</span></td>
                            <td class="num">
                                <strong class="{{ $m->quantity >= 0 ? 'qty-positive' : 'qty-negative' }}">
                                    {{ $m->quantity > 0 ? '+' : '' }}{{ qty($m->quantity, $m->product->sale_unit) }}
                                </strong>
                            </td>
                            <td class="muted">{{ $m->user->name ?? 'Sistema' }}</td>
                            <td class="muted">{{ $m->reason ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $movements->links() }}
        </div>
    @endif
</div>
@endsection
