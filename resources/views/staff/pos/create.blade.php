@extends('layouts.staff', ['title' => 'Venta en Tienda (Caja)'])

@section('content')
<div class="card" style="max-width: 700px;">
    <div class="card-title">
        <div>
            <h2>Registro de Venta en Tienda Física</h2>
            <p class="muted" style="margin: 0; font-size: .88rem;">Permite al dependiente/cajero registrar ventas físicas para descontar el stock en tiempo real.</p>
        </div>
        <span class="badge badge-amber">Alcance pendiente de aprobación (FR-POS-01)</span>
    </div>

    <form action="{{ route('staff.pos.store') }}" method="POST">
        @csrf

        <div class="form-grid">
            <div class="field field-full">
                <label for="location_id">Ubicación de la tienda física *</label>
                <select id="location_id" name="location_id" required>
                    @foreach($storeLocations as $store)
                        <option value="{{ $store->id }}" {{ old('location_id') == $store->id ? 'selected' : '' }}>
                            🏬 {{ $store->name }} — {{ $store->address }}
                        </option>
                    @endforeach
                </select>
                @error('location_id') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field field-full">
                <label for="product_id">Producto vendido *</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Selecciona el producto</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} — {{ eur($product->price) }} / {{ $product->unitLabel() }}
                        </option>
                    @endforeach
                </select>
                @error('product_id') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="quantity">Cantidad cobrada *</label>
                <input type="number" step="0.001" min="0.001" id="quantity" name="quantity" value="{{ old('quantity', '1') }}" required>
                @error('quantity') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-actions" style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary btn-lg">Registrar venta y descontar stock</button>
        </div>
    </form>
</div>
@endsection
