@extends('layouts.staff')

@section('title', 'Gestión de Usuarios y Empleados')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Usuarios y Empleados</h1>
        <p class="page-subtitle">Control de accesos y roles (Admin, Mozo de Almacén, Cajero, Cliente)</p>
    </div>
    <div>
        <a href="{{ route('staff.users.create') }}" class="btn btn-primary">+ Nuevo Usuario</a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <form method="GET" action="{{ route('staff.users.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 220px; margin-bottom: 0;">
            <label for="q" class="form-label">Buscar Usuario</label>
            <input type="text" name="q" id="q" class="form-control" value="{{ request('q') }}" placeholder="Nombre o email...">
        </div>

        <div class="form-group" style="min-width: 180px; margin-bottom: 0;">
            <label for="role" class="form-label">Rol</label>
            <select name="role" id="role" class="form-control">
                <option value="">-- Todos los roles --</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="warehouse" {{ request('role') === 'warehouse' ? 'selected' : '' }}>Mozo de Almacén</option>
                <option value="cashier" {{ request('role') === 'cashier' ? 'selected' : '' }}>Cajero de Tienda</option>
                <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Cliente</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('staff.users.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

<div class="card">
    @if($users->isEmpty())
        <div style="text-align: center; padding: 3rem 1rem;">
            <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 1rem;">No se encontraron usuarios.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Alta</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                @if($user->id === auth()->id())
                                    <span class="badge" style="background: #e0e7ff; color: #3730a3; margin-left: 0.25rem;">Tú</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleColors = [
                                        'admin' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'label' => 'Administrador'],
                                        'warehouse' => ['bg' => '#e0e7ff', 'text' => '#3730a3', 'label' => 'Almacén'],
                                        'cashier' => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'Cajero'],
                                        'customer' => ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => 'Cliente'],
                                    ];
                                    $rc = $roleColors[$user->role] ?? ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => $user->role];
                                @endphp
                                <span class="badge" style="background-color: {{ $rc['bg'] }}; color: {{ $rc['text'] }};">
                                    {{ $rc['label'] }}
                                </span>
                            </td>
                            <td>{{ $user->phone ?: '—' }}</td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge" style="background-color: #dcfce7; color: #166534;">Activo</span>
                                @else
                                    <span class="badge" style="background-color: #fee2e2; color: #991b1b;">Desactivado</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="{{ route('staff.users.edit', $user) }}" class="btn btn-secondary btn-sm">Editar</a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('staff.users.toggle', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline' : 'btn-primary' }}">
                                                {{ $user->is_active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
