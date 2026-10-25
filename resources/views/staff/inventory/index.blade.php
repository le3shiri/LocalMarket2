@extends('layouts.staff', ['title' => 'Control de Inventario'])

@section('content')
<div class="card">
    <div class="card-title">
        <div>
            <h2>Inventario por Ubicación</h2>
            <p class="muted" style="margin: 0; font-size: .88rem;">Consulta existencias, reservas y disponibilidad en tiempo real.</p>
        </div>
        <div class="actions">
            <a href="{{ route('staff.inventory.receipt') }}" class="btn btn-primary btn-sm">+ Entrada Proveedor</a>
            <a href="{{ route('staff.inventory.transfer') }}" class="btn btn-outline btn-sm">⇄ Traspaso</a>
            <a href="{{ route('staff.inventory.adjustment') }}" class="btn btn-ghost btn-sm">± Ajuste</a>
            <a href="{{ route('staff.inventory.movements') }}" class="btn btn-outline btn-sm">📜 Movimientos</a>
        </div>
    </div>

    <div class="chips" style="margin-bottom: 1.5rem;">
        @foreach($locations as $loc)
            <a href="{{ route('staff.inventory.index', ['location_id' => $loc->id]) }}" 
               class="chip {{ $selectedLocation && $selectedLocation->id == $loc->id ? 'active' : '' }}">
                {{ $loc->isWarehouse() ? '🏭' : '🏬' }} {{ $loc->name }}
                @if($loc->is_online_source)
                    <span class="badge badge-green" style="font-size: .68rem;">Online</span>
                @endif
            </a>
        @endforeach
    </div>

    @if(!$selectedLocation || empty($stockLevels) || $stockLevels->isEmpty())
        <div class="empty">
            <p>No hay existencias registradas en esta ubicación.</p>
            <a href="{{ route('staff.inventory.receipt') }}" class="btn btn-primary btn-sm">+ Registrar primera entrada</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="num">Físico</th>
                        <th class="num">Reservado</th>
                        <th class="num">Disponible</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockLevels as $level)
                        <tr>
                            <td>
                                <strong>{{ $level->product->name }}</strong>
                                <div class="muted" style="font-size: .78rem;">{{ $level->product->shelf_location ?: 'Sin ubicación' }}</div>
                            </td>
                            <td>{{ $level->product->category->name }}</td>
                            <td class="num"><strong>{{ qty($level->quantity, $level->product->sale_unit) }}</strong></td>
                            <td class="num" style="color: var(--orange-600);">{{ qty($level->reserved, $level->product->sale_unit) }}</td>
                            <td class="num">
                                <strong class="{{ $level->available <= $level->product->low_stock_threshold ? 'qty-negative' : 'qty-positive' }}">
                                    {{ qty($level->available, $level->product->sale_unit) }}
                                </strong>
                            </td>
                            <td>
                                @if($level->available <= 0)
                                    <span class="badge badge-red">Agotado</span>
                                @elseif($level->available <= $level->product->low_stock_threshold)
                                    <span class="badge badge-amber">Stock Bajo</span>
                                @else
                                    <span class="badge badge-green">Disponible</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $stockLevels->links() }}
        </div>
    @endif
</div>
@endsection
