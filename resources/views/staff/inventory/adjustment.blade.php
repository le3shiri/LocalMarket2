@extends('layouts.staff', ['title' => 'Ajuste de Inventario'])

@section('content')
<div class="card" style="max-width: 700px;">
    <div class="card-title">
        <h2>Ajuste Manual de Inventario</h2>
        <a href="{{ route('staff.inventory.index') }}" class="btn btn-sm btn-ghost">&larr; Volver</a>
    </div>

    <form action="{{ route('staff.inventory.adjustment') }}" method="POST">
        @csrf

        <div class="form-grid">
            <div class="field field-full">
                <label for="product_id">Producto a regularizar *</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Selecciona el producto</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->unitLabel() }})
                        </option>
                    @endforeach
                </select>
                @error('product_id') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="location_id">Ubicación *</label>
                <select id="location_id" name="location_id" required>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->isWarehouse() ? '🏭' : '🏬' }} {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
                @error('location_id') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="quantity_delta">Diferencia / Delta (+ o -) *</label>
                <input type="number" step="0.001" id="quantity_delta" name="quantity_delta" value="{{ old('quantity_delta') }}" required placeholder="Ej: -2 para rotura, +5 por recuento">
                <span class="hint">Indica un número positivo si hay más producto o negativo si hay merma o rotura.</span>
                @error('quantity_delta') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field field-full">
                <label for="reason">Motivo obligatorio del ajuste *</label>
                <input type="text" id="reason" name="reason" value="{{ old('reason') }}" required placeholder="Ej: Rotura accidental en pasillo / Corrección recuento mensual">
                @error('reason') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Aplicar ajuste</button>
            <a href="{{ route('staff.inventory.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
