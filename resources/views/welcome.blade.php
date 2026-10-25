@extends('layouts.app')

@section('content')
<div class="container">
    <div class="hero">
        <div class="hero-content">
            <span class="eyebrow">
                <x-icon name="map-pin" size="14" />
                <span>Barcelona · Supermercado de Proximidad</span>
            </span>
            <h1>Alimentación fresca y de calidad en tu mesa</h1>
            <p>Productos de productores locales, frutas y verduras seleccionadas a diario, carne y lácteos frescos. Entrega rápida en Barcelona o recogida gratis en nuestra tienda física.</p>
            <div class="hero-actions">
                <a href="{{ route('catalog.index') }}" class="btn btn-primary btn-lg" style="font-weight: 700;">
                    <x-icon name="shopping-bag" size="18" />
                    <span>Explorar catálogo</span>
                </a>
                <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">Acceso clientes / personal</a>
            </div>
        </div>
    </div>

    <div class="features">
        <div class="feature">
            <div class="feature-icon" style="background: var(--emerald-50); color: var(--emerald-700);">
                <x-icon name="sparkles" size="22" />
            </div>
            <div>
                <strong>100% Fresco y Local</strong>
                <small>Seleccionamos producto de proximidad cada mañana.</small>
            </div>
        </div>
        <div class="feature">
            <div class="feature-icon" style="background: var(--blue-50); color: var(--blue-600);">
                <x-icon name="truck" size="22" />
            </div>
            <div>
                <strong>Entrega en Barcelona</strong>
                <small>Envíos en códigos 08001–08042 (gratis a partir de 50 €).</small>
            </div>
        </div>
        <div class="feature">
            <div class="feature-icon" style="background: var(--amber-50); color: var(--amber-600);">
                <x-icon name="store" size="22" />
            </div>
            <div>
                <strong>Recogida en Tienda</strong>
                <small>Preparamos tu pedido para recoger sin esperas ni coste.</small>
            </div>
        </div>
        <div class="feature">
            <div class="feature-icon" style="background: var(--emerald-50); color: var(--emerald-700);">
                <x-icon name="shield-check" size="22" />
            </div>
            <div>
                <strong>Stock en Tiempo Real</strong>
                <small>Inventario coordinado para evitar roturas y esperas.</small>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="section-head">
            <h2>Categorías principales</h2>
            <a href="{{ route('catalog.index') }}" class="btn btn-sm btn-ghost">
                <span>Ver todas</span>
                <x-icon name="arrow-right" size="14" />
            </a>
        </div>

        <div class="category-grid">
            @foreach($categories as $cat)
                <a href="{{ route('catalog.index', ['categoria' => $cat->slug]) }}" class="category-card">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: var(--emerald-50); color: var(--emerald-700); display: grid; place-items: center; margin: 0 auto 0.6rem;">
                        <x-icon name="folder" size="20" />
                    </div>
                    <strong>{{ $cat->name }}</strong>
                </a>
            @endforeach
        </div>
    </section>

    <section class="section">
        <div class="section-head">
            <h2>Productos destacados</h2>
            <a href="{{ route('catalog.index') }}" class="btn btn-sm btn-ghost">
                <span>Ver catálogo completo</span>
                <x-icon name="arrow-right" size="14" />
            </a>
        </div>

        <div class="product-grid">
            @forelse($featuredProducts as $product)
                <div class="product-card">
                    <div class="product-media">
                        <div class="product-fallback">
                            <x-icon name="shopping-bag" size="28" />
                        </div>
                        <span class="badge badge-green">{{ $product->category->name ?? 'General' }}</span>
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
