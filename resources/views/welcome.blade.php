@extends('layouts.app')

@section('content')
<div class="container">
    <div class="hero" style="--hero-img: linear-gradient(135deg, rgba(15, 61, 46, 0.95), rgba(29, 107, 79, 0.85));">
        <div class="hero-content">
            <span class="eyebrow">📍 Barcelona · Supermercado de Proximidad</span>
            <h1>Alimentación fresca y de calidad en tu mesa</h1>
            <p>Productos de productores locales, frutas y verduras seleccionadas a diario, carne y lácteos frescos. Entrega rápida en Barcelona o recogida gratis en nuestra tienda física.</p>
            <div class="hero-actions">
                <a href="{{ route('catalog.index') }}" class="btn btn-accent btn-lg">Explorar catálogo</a>
                <a href="{{ route('login') }}" class="btn btn-outline btn-lg">Acceso clientes / personal</a>
            </div>
        </div>
    </div>

    <div class="features">
        <div class="feature">
            <div class="feature-icon">🌿</div>
            <div>
                <strong>100% Fresco y Local</strong>
                <small>Seleccionamos producto de proximidad cada mañana.</small>
            </div>
        </div>
        <div class="feature">
            <div class="feature-icon">🚚</div>
            <div>
                <strong>Entrega en Barcelona</strong>
                <small>Envíos en códigos 08001–08042 (gratis a partir de 50 €).</small>
            </div>
        </div>
        <div class="feature">
            <div class="feature-icon">🏬</div>
            <div>
                <strong>Recogida en Tienda</strong>
                <small>Preparamos tu pedido para recoger sin esperas ni coste.</small>
            </div>
        </div>
        <div class="feature">
            <div class="feature-icon">🔒</div>
            <div>
                <strong>Stock en Tiempo Real</strong>
                <small>Inventario coordinado para evitar roturas y esperas.</small>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="section-head">
            <h2>Categorías principales</h2>
            <a href="{{ route('catalog.index') }}" class="btn btn-sm btn-ghost">Ver todas &rarr;</a>
        </div>

        <div class="category-grid">
            @foreach($categories as $cat)
                <a href="{{ route('catalog.index', ['categoria' => $cat->slug]) }}" class="category-card">
                    <span class="emoji">{{ $cat->icon }}</span>
                    <strong>{{ $cat->name }}</strong>
                </a>
            @endforeach
        </div>
    </section>

    <section class="section">
        <div class="section-head">
            <h2>Productos destacados</h2>
            <a href="{{ route('catalog.index') }}" class="btn btn-sm btn-ghost">Ver catálogo completo &rarr;</a>
        </div>

        <div class="product-grid">
            @forelse($featuredProducts as $product)
                <div class="product-card">
                    <div class="product-media">
                        <div class="product-fallback">{{ $product->category->icon ?? '🍎' }}</div>
                        <span class="badge badge-green">{{ $product->category->name }}</span>
                    </div>
                    <div class="product-body">
                        <span class="product-cat">{{ $product->unitLabel() }}</span>
                        <a href="{{ route('catalog.show', $product->slug) }}" class="product-name">{{ $product->name }}</a>
                        <p class="muted" style="font-size: .85rem; margin: 0;">{{ Str::limit($product->description, 55) }}</p>
                        <div class="product-foot">
                            <div class="price">
                                {{ eur($product->price) }}
                                <small>/ {{ $product->sale_unit == 'kg' ? 'kg' : 'ud.' }}</small>
                            </div>
                            <a href="{{ route('catalog.show', $product->slug) }}" class="btn btn-sm btn-outline">Ver ficha</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card empty field-full">
                    <p class="muted">Aún no hay productos disponibles.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
