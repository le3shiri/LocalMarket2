@extends('layouts.staff', ['title' => 'Lotes y Caducidades'])

@section('content')
<div class="card">
    <div class="card-title">
        <h2>Lotes y Control de Caducidades (FEFO)</h2>
    </div>

    <div class="chips" style="margin-bottom: 1.5rem;">
        <a href="{{ route('staff.batches.index') }}" class="chip {{ !request('location_id') ? 'active' : '' }}">
            Todas las ubicaciones
        </a>
        @foreach($locations as $loc)
            <a href="{{ route('staff.batches.index', ['location_id' => $loc->id]) }}" 
               class="chip {{ request('location_id') == $loc->id ? 'active' : '' }}">
                {{ $loc->isWarehouse() ? '🏭' : '🏬' }} {{ $loc->name }}
            </a>
        @endforeach
    </div>

    @if($batches->isEmpty())
        <div class="empty">
            <p>No hay lotes con existencias activas.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Ubicación</th>
                        <th>Lote</th>
                        <th>Caducidad</th>
                        <th class="num">Cantidad</th>
                        <th>Alerta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $b)
                        <tr>
                            <td><strong>{{ $b->product->name }}</strong></td>
                            <td>{{ $b->location->name }}</td>
                            <td><code>{{ $b->lot_code }}</code></td>
                            <td>{{ fdate($b->expiry_date, false) }}</td>
                            <td class="num"><strong>{{ qty($b->quantity, $b->product->sale_unit) }}</strong></td>
                            <td>
                                @if($b->isExpired())
                                    <span class="badge badge-red">Caducado</span>
                                @elseif($b->isNearExpiry(config('localmarket.near_expiry_days', 3)))
                                    <span class="badge badge-amber">Próximo a caducar (≤ 3 días)</span>
                                @else
                                    <span class="badge badge-green">En fecha</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $batches->links() }}
        </div>
    @endif
</div>
@endsection
