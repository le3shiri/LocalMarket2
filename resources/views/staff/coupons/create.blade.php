@extends('layouts.staff')

@section('title', 'Nuevo Cupón')

@section('content')
<div class="content-header">
    <div>
        <a href="{{ route('staff.coupons.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 0.5rem; display: inline-block;">&larr; Volver a cupones</a>
        <h1 class="page-title">Crear Nuevo Cupón</h1>
        <p class="page-subtitle">Configura los parámetros, límites y vigencia de la promoción</p>
    </div>
</div>

<div class="card" style="max-width: 720px;">
    <form action="{{ route('staff.coupons.store') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
            <div class="form-group">
                <label for="code" class="form-label">Código del Cupón *</label>
                <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" required placeholder="BIENVENIDA10" style="text-transform: uppercase; font-family: monospace; font-weight: 700;">
                @error('code') <span class="error" style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="type" class="form-label">Tipo de Descuento *</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
                    <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Importe Fijo (€)</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
            <div class="form-group">
                <label for="value" class="form-label">Valor del Descuento *</label>
                <input type="number" step="0.01" min="0.01" name="value" id="value" class="form-control" value="{{ old('value', '10') }}" required placeholder="Ej. 10 para 10% o 5 para 5€">
                @error('value') <span class="error" style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="min_order_amount" class="form-label">Pedido Mínimo (€)</label>
                <input type="number" step="0.01" min="0" name="min_order_amount" id="min_order_amount" class="form-control" value="{{ old('min_order_amount', '0') }}" required placeholder="0.00">
                @error('min_order_amount') <span class="error" style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Descripción Interna</label>
            <input type="text" name="description" id="description" class="form-control" value="{{ old('description') }}" placeholder="Ej: 10% de descuento en la primera compra">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
            <div class="form-group">
                <label for="max_uses" class="form-label">Límite de Usos</label>
                <input type="number" min="1" name="max_uses" id="max_uses" class="form-control" value="{{ old('max_uses') }}" placeholder="Vacío = Ilimitado">
            </div>

            <div class="form-group">
                <label for="starts_at" class="form-label">Fecha Inicio</label>
                <input type="datetime-local" name="starts_at" id="starts_at" class="form-control" value="{{ old('starts_at') }}">
            </div>

            <div class="form-group">
                <label for="expires_at" class="form-label">Fecha Caducidad</label>
                <input type="datetime-local" name="expires_at" id="expires_at" class="form-control" value="{{ old('expires_at') }}">
            </div>
        </div>

        <div class="form-group" style="margin-top: 0.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span class="form-label" style="margin: 0;">Activar cupón inmediatamente</span>
            </label>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Guardar Cupón</button>
            <a href="{{ route('staff.coupons.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
