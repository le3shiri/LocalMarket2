@extends('layouts.staff')

@section('title', 'Nuevo Usuario')

@section('content')
<div class="content-header">
    <div>
        <a href="{{ route('staff.users.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.5rem; display: inline-block;">&larr; Volver a usuarios</a>
        <h1 class="page-title">Crear Nuevo Usuario / Empleado</h1>
        <p class="page-subtitle">Asigna roles y credenciales para el personal de la tienda o almacén</p>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    <form action="{{ route('staff.users.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">Nombre y Apellidos *</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="Juan Pérez">
            @error('name') <span class="error" style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico *</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required placeholder="juan@localmarket.test">
                @error('email') <span class="error" style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Rol en el Sistema *</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Cajero de Tienda (TPV)</option>
                    <option value="warehouse" {{ old('role') === 'warehouse' ? 'selected' : '' }}>Mozo de Almacén (Inventario)</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador (Total)</option>
                    <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>Cliente</option>
                </select>
                @error('role') <span class="error" style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="phone" class="form-label">Teléfono de Contacto</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" placeholder="612 34 56 78">
            @error('phone') <span class="error" style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
            <div class="form-group">
                <label for="password" class="form-label">Contraseña *</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="Mínimo 8 caracteres">
                @error('password') <span class="error" style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmar Contraseña *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Repite la contraseña">
            </div>
        </div>

        <div class="form-group" style="margin-top: 0.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span class="form-label" style="margin: 0;">Usuario activo (puede iniciar sesión)</span>
            </label>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
            <a href="{{ route('staff.users.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
