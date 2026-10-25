@extends('layouts.staff')

@section('title', 'Registro de Auditoría')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Pistas de Auditoría</h1>
        <p class="page-subtitle">Registro cronológico inmutable de operaciones sensibles (pedidos, inventario, usuarios y cupones)</p>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <form method="GET" action="{{ route('staff.audit.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label for="action" class="form-label">Filtrar por Acción</label>
            <input type="text" name="action" id="action" class="form-control" value="{{ request('action') }}" placeholder="Ej: stock, user, order, payment...">
        </div>

        <div class="form-group" style="min-width: 160px; margin-bottom: 0;">
            <label for="date_from" class="form-label">Desde</label>
            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>

        <div class="form-group" style="min-width: 160px; margin-bottom: 0;">
            <label for="date_to" class="form-label">Hasta</label>
            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('staff.audit.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

<div class="card">
    @if($logs->isEmpty())
        <div style="text-align: center; padding: 3rem 1rem;">
            <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 1rem;">No se encontraron registros de auditoría para los filtros aplicados.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Acción</th>
                        <th>Usuario / Actor</th>
                        <th>Entidad Afectada</th>
                        <th>Detalles / Metadatos</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td style="white-space: nowrap; font-size: 0.85rem; color: #475569;">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td>
                                @php
                                    $actionPrefix = explode('.', $log->action)[0] ?? '';
                                    $badgeStyle = match($actionPrefix) {
                                        'user' => 'background: #fef2f2; color: #991b1b;',
                                        'stock' => 'background: #e0e7ff; color: #3730a3;',
                                        'order' => 'background: #fef3c7; color: #92400e;',
                                        'payment' => 'background: #dcfce7; color: #166534;',
                                        'coupon' => 'background: #f3e8ff; color: #6b21a8;',
                                        default => 'background: #f1f5f9; color: #334155;'
                                    };
                                @endphp
                                <span class="badge" style="{{ $badgeStyle }} font-family: monospace; font-size: 0.85rem;">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td>
                                @if($log->user)
                                    <strong>{{ $log->user->name }}</strong>
                                    <small style="display: block; color: #64748b;">{{ $log->user->role }}</small>
                                @else
                                    <span style="color: #64748b; font-style: italic;">Sistema</span>
                                @endif
                            </td>
                            <td>
                                @if($log->auditable_type)
                                    <span style="font-size: 0.85rem; color: #334155;">
                                        {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8;">—</span>
                                @endif
                            </td>
                            <td style="max-width: 320px;">
                                @if(!empty($log->payload))
                                    <div style="font-family: monospace; font-size: 0.78rem; background: #f8fafc; padding: 0.4rem 0.6rem; border-radius: 4px; border: 1px solid #e2e8f0; overflow-x: auto; white-space: pre-wrap;">
                                        {{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                    </div>
                                @else
                                    <span style="color: #94a3b8;">Sin datos adicionales</span>
                                @endif
                            </td>
                            <td style="font-size: 0.8rem; color: #64748b; font-family: monospace;">
                                {{ $log->ip_address ?: '127.0.0.1' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
