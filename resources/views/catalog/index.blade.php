@extends('layouts.app', ['title' => 'Catálogo de Productos'])

@section('content')
<div class="container page">
    <div class="page-head">
        <div>
            <h1>Catálogo de productos</h1>
            <p>
                @if($currentCategory)
                    Mostrando productos en la categoría <strong>{{ $currentCategory->name }}</strong>
                @elseif(request('q'))
                    Resultados de búsqueda para "<strong>{{ request('q') }}</strong>"
                @else
                    Alimentación fresca y productos de proximidad en Barcelona
                @endif
            </p>
        </div>
        @if(request('categoria') || request('q'))
            <a href="{{ route('catalog.index') }}" class="btn btn-sm btn-secondary">
                <x-icon name="x" size="14" />
                <span>Limpiar filtros</span>
            </a>
        @endif
    </div>

    <div class="chips" style="margin-bottom: 2rem;">
        <a href="{{ route('catalog.index', ['q' => request('q')]) }}" class="chip {{ !request('categoria') ? 'active' : '' }}">
            <x-icon name="shopping-bag" size="14" />
            <span>Todos los productos</span>
        </a>
        @foreach($categories as $category)
            <a href="{{ route('catalog.index', ['categoria' => $category->slug, 'q' => request('q')]) }}" 
               class="chip {{ request('categoria') === $category->slug ? 'active' : '' }}">
                <x-icon name="folder" size="14" />
                <span>{{ $category->name }}</span>
            </a>
        @endforeach
    </div>

    @if($products->isEmpty())
        <div class="card empty">
            <div class="empty-icon">
                <x-icon name="search" size="28" />
            </div>
            <h3>No se encontraron productos</h3>
            <p class="muted">Prueba a buscar con otras palabras o selecciona una categoría diferente.</p>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary" style="margin-top: 1.25rem;">
                <x-icon name="shopping-bag" size="16" />
                <span>Ver todos los productos</span>
            </a>
        </div>
    @else
        <div class="product-grid">
            @foreach($products as $product)
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
                        <p class="muted" style="font-size: .85rem; margin: 0;">{{ Str::limit($product->description, 60) }}</p>
                        
                        <div class="product-foot">
                            <div class="price">
                                {{ eur($product->price) }}
                                <small>/ {{ $product->sale_unit == 'kg' ? 'kg' : 'ud.' }}</small>
                            </div>
                            <a href="{{ route('catalog.show', $product->slug) }}" class="btn btn-sm btn-primary">Ver detalle</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 2rem;">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
