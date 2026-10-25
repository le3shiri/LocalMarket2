@extends('layouts.staff')

@section('title', 'Gestión de Cupones')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Cupones y Promociones</h1>
        <p class="page-subtitle">Crea y supervisa cupones de descuento porcentuales o de importe fijo</p>
    </div>
    <div>
        <a href="{{ route('staff.coupons.create') }}" class="btn btn-primary">+ Nuevo Cupón</a>
    </div>
</div>

<div class="card">
    @if($coupons->isEmpty())
        <div style="text-align: center; padding: 3rem 1rem;">
            <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 1rem;">No hay cupones registrados en la plataforma.</p>
            <a href="{{ route('staff.coupons.create') }}" class="btn btn-primary">Crear el primer cupón</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo y Valor</th>
                        <th>Pedido Mínimo</th>
                        <th>Usos / Límite</th>
                        <th>Vigencia</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coupons as $coupon)
                        <tr>
                            <td>
                                <strong style="font-family: monospace; font-size: 1rem; color: #0284c7; background: #f0f9ff; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                    {{ $coupon->code }}
                                </strong>
                                @if($coupon->description)
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;">{{ $coupon->description }}</div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $coupon->valueFormatted() }}</strong>
                                <small style="color: #64748b; display: block;">{{ $coupon->typeLabel() }}</small>
                            </td>
                            <td>{{ number_format($coupon->min_order_amount, 2, ',', '.') }} €</td>
                            <td>
                                <strong>{{ $coupon->uses_count }}</strong>
                                <span style="color: #64748b;">/ {{ $coupon->max_uses ? $coupon->max_uses : '∞' }}</span>
                            </td>
                            <td>
                                <small style="display: block; color: #475569;">
                                    {{ $coupon->starts_at ? 'Desde ' . $coupon->starts_at->format('d/m/Y') : 'Desde siempre' }}
                                </small>
                                <small style="display: block; color: #475569;">
                                    {{ $coupon->expires_at ? 'Hasta ' . $coupon->expires_at->format('d/m/Y') : 'Sin caducidad' }}
                                </small>
                            </td>
                            <td>
                                @if($coupon->is_active)
                                    <span class="badge" style="background-color: #dcfce7; color: #166534;">Activo</span>
                                @else
                                    <span class="badge" style="background-color: #fee2e2; color: #991b1b;">Inactivo</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="{{ route('staff.coupons.edit', $coupon) }}" class="btn btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('staff.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cupón?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $coupons->links() }}
        </div>
    @endif
</div>
@endsection
