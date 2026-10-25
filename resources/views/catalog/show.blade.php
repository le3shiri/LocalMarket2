@extends('layouts.app', ['title' => $product->name])

@section('content')
<div class="container page">
    <div class="breadcrumbs">
        <a href="{{ route('home') }}">Inicio</a> &rsaquo;
        <a href="{{ route('catalog.index') }}">Catálogo</a> &rsaquo;
        <a href="{{ route('catalog.index', ['categoria' => $product->category->slug]) }}">{{ $product->category->name }}</a> &rsaquo;
        <span>{{ $product->name }}</span>
    </div>

    <div class="product-detail">
        <div class="product-media" style="aspect-ratio: 4/3; display: grid; place-items: center; background: #eef3ed;">
            <div style="font-size: 6rem;">{{ $product->category->icon ?? '🛒' }}</div>
        </div>

        <div>
            <span class="badge badge-green">{{ $product->category->name }}</span>
            <h1 style="margin-top: .6rem; margin-bottom: .2rem;">{{ $product->name }}</h1>
            <div class="detail-price">
                {{ eur($product->price) }}
                <small style="font-size: 1rem; color: var(--muted); font-weight: 500;">/ {{ $product->unitLabel() }}</small>
            </div>

            <p style="font-size: 1.05rem; color: var(--ink-soft); line-height: 1.6;">
                {{ $product->description ?: 'Producto fresco de calidad garantizada por LocalMarket Barcelona.' }}
            </p>

            <div class="meta-list">
                <div>
                    <span>Formato de venta:</span>
                    <strong>{{ $product->unitLabel() }}</strong>
                </div>
                <div>
                    <span>Ubicación en tienda:</span>
                    <strong>{{ $product->shelf_location ?: 'Zona de frescos' }}</strong>
                </div>
                <div>
                    <span>Control de caducidad:</span>
                    <strong>{{ $product->tracks_expiry ? 'Sí (control por lote/caducidad)' : 'No aplica' }}</strong>
                </div>
                <div>
                    <span>Disponibilidad online:</span>
                    <strong style="color: var(--green-700);">En stock (Servicio Barcelona)</strong>
                </div>
            </div>

            <div style="margin-top: 1.5rem; padding: 1.5rem; background: var(--paper); border: 1px solid var(--border); border-radius: var(--radius);">
                <form method="POST" action="{{ route('cart.add') }}" style="display: flex; flex-direction: column; gap: 1rem;">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <label for="quantity" style="font-weight: 600; color: var(--ink);">Cantidad:</label>
                        <input type="number" 
                               name="quantity" 
                               id="quantity" 
                               class="form-control" 
                               value="{{ $product->unit === 'kg' ? '0.5' : '1' }}" 
                               min="{{ $product->unit === 'kg' ? '0.1' : '1' }}" 
                               step="{{ $product->unit === 'kg' ? '0.05' : '1' }}" 
                               style="width: 120px;">
                        <span style="color: var(--muted); font-size: 0.95rem;">{{ $product->unitLabel() }}</span>
                    </div>

                    <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 0.5rem;">
                        <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                            🛒 Añadir a la cesta
                        </button>
                        <a href="{{ route('catalog.index') }}" class="btn btn-outline btn-lg">&larr; Seguir comprando</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($relatedProducts->isNotEmpty())
        <section class="section" style="margin-top: 4rem;">
            <h2>Productos similares en {{ $product->category->name }}</h2>
            <div class="product-grid" style="margin-top: 1rem;">
                @foreach($relatedProducts as $rel)
                    <div class="product-card">
                        <div class="product-media">
                            <div class="product-fallback">{{ $rel->category->icon ?? '🍎' }}</div>
                        </div>
                        <div class="product-body">
                            <a href="{{ route('catalog.show', $rel->slug) }}" class="product-name">{{ $rel->name }}</a>
                            <div class="product-foot">
                                <div class="price">{{ eur($rel->price) }}</div>
                                <a href="{{ route('catalog.show', $rel->slug) }}" class="btn btn-sm btn-outline">Ver</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
