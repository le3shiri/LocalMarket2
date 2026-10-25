@extends('layouts.staff', ['title' => 'Editar Producto'])

@section('content')
<div class="card" style="max-width: 800px;">
    <div class="card-title">
        <h2>Editar Producto: {{ $product->name }}</h2>
        <a href="{{ route('staff.products.index') }}" class="btn btn-sm btn-ghost">&larr; Volver</a>
    </div>

    <form action="{{ route('staff.products.update', $product) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="field field-full">
                <label for="name">Nombre del producto *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="category_id">Categoría *</label>
                <select id="category_id" name="category_id" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="sale_unit">Formato de venta *</label>
                <select id="sale_unit" name="sale_unit" required>
                    @foreach($units as $key => $label)
                        <option value="{{ $key }}" {{ old('sale_unit', $product->sale_unit) == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('sale_unit') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="price">Precio en EUR (€) *</label>
                <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                @error('price') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="low_stock_threshold">Umbral de stock bajo *</label>
                <input type="number" step="0.001" min="0" id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" required>
                @error('low_stock_threshold') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field field-full">
                <label for="shelf_location">Ubicación en tienda / pasillo</label>
                <input type="text" id="shelf_location" name="shelf_location" value="{{ old('shelf_location', $product->shelf_location) }}">
                @error('shelf_location') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field field-full">
                <label for="description">Descripción detallada</label>
                <textarea id="description" name="description">{{ old('description', $product->description) }}</textarea>
                @error('description') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="margin: 1.5rem 0; display: flex; flex-direction: column; gap: .7rem;">
            <div class="check">
                <input type="checkbox" id="tracks_expiry" name="tracks_expiry" value="1" {{ old('tracks_expiry', $product->tracks_expiry) ? 'checked' : '' }}>
                <label for="tracks_expiry"><strong>Controlar lotes y caducidad</strong> (perecedero: exige lote y fecha)</label>
            </div>

            <div class="check">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                <label for="is_active"><strong>Producto activo</strong></label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('staff.products.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
