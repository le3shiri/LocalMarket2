@extends('layouts.staff', ['title' => 'Entrada de Mercancía'])

@section('content')
<div class="card" style="max-width: 700px;">
    <div class="card-title">
        <h2>Registrar Entrada de Mercancía</h2>
        <a href="{{ route('staff.inventory.index') }}" class="btn btn-sm btn-ghost">&larr; Volver</a>
    </div>

    <form action="{{ route('staff.inventory.receipt') }}" method="POST">
        @csrf

        <div class="form-grid">
            <div class="field field-full">
                <label for="product_id">Producto recibido *</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Selecciona el producto</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-expiry="{{ $product->tracks_expiry ? '1' : '0' }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->unitLabel() }}) {{ $product->tracks_expiry ? '— [Perecedero: exige lote/caducidad]' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('product_id') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="location_id">Ubicación de destino *</label>
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
                <label for="quantity">Cantidad recibida *</label>
                <input type="number" step="0.001" min="0.001" id="quantity" name="quantity" value="{{ old('quantity') }}" required placeholder="Ej: 20">
                @error('quantity') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field field-full">
                <label for="reason">Motivo / Factura proveedor / Albarán *</label>
                <input type="text" id="reason" name="reason" value="{{ old('reason') }}" required placeholder="Ej: Albarán ALB-2026-904 Frutas Lleida">
                @error('reason') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="lot_code">Número de lote (si es perecedero)</label>
                <input type="text" id="lot_code" name="lot_code" value="{{ old('lot_code') }}" placeholder="Ej: L-261025-01">
                @error('lot_code') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="expiry_date">Fecha de caducidad (si es perecedero)</label>
                <input type="date" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                @error('expiry_date') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Registrar entrada</button>
            <a href="{{ route('staff.inventory.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
