@extends('layouts.staff', ['title' => 'Traspaso de Mercancía'])

@section('content')
<div class="card" style="max-width: 700px;">
    <div class="card-title">
        <h2>Traspaso de Mercancía entre Ubicaciones</h2>
        <a href="{{ route('staff.inventory.index') }}" class="btn btn-sm btn-ghost">&larr; Volver</a>
    </div>

    <form action="{{ route('staff.inventory.transfer') }}" method="POST">
        @csrf

        <div class="form-grid">
            <div class="field field-full">
                <label for="product_id">Producto a traspasar *</label>
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
                <label for="from_location_id">Ubicación Origen *</label>
                <select id="from_location_id" name="from_location_id" required>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('from_location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->isWarehouse() ? '🏭' : '🏬' }} {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
                @error('from_location_id') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="to_location_id">Ubicación Destino *</label>
                <select id="to_location_id" name="to_location_id" required>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('to_location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->isWarehouse() ? '🏭' : '🏬' }} {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
                @error('to_location_id') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="quantity">Cantidad a traspasar *</label>
                <input type="number" step="0.001" min="0.001" id="quantity" name="quantity" value="{{ old('quantity') }}" required placeholder="Ej: 10">
                @error('quantity') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field field-full">
                <label for="reason">Motivo del traspaso *</label>
                <input type="text" id="reason" name="reason" value="{{ old('reason', 'Reposición diaria de tienda física') }}" required>
                @error('reason') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Confirmar traspaso</button>
            <a href="{{ route('staff.inventory.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
