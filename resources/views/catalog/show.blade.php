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

            <div style="margin-top: 1.5rem; padding: 1.2rem; background: var(--paper); border: 1px solid var(--border); border-radius: var(--radius);">
                <p class="muted" style="font-size: .88rem; margin-bottom: 1rem;">
                    * La funcionalidad del carrito de compra y reserva de existencias se activará en el módulo de Inventario y Pedidos (Sprint 4).
                </p>
                <div class="qty-row">
                    <a href="{{ route('catalog.index') }}" class="btn btn-outline btn-lg">&larr; Seguir comprando</a>
                </div>
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
