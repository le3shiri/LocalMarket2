@extends('layouts.app', ['title' => 'Cesta de la Compra'])

@section('content')
<div class="container page">
    <div class="page-head">
        <div>
            <h1>Mi cesta de la compra</h1>
            <p>Revisa tus productos y tramita tu compra con entrega rápida en Barcelona</p>
        </div>
        <a href="{{ route('catalog.index') }}" class="btn btn-sm btn-secondary">
            <x-icon name="arrow-left" size="14" />
            <span>Seguir comprando</span>
        </a>
    </div>

    @if(empty($cart['items']))
        <div class="card empty">
            <div class="empty-icon">
                <x-icon name="shopping-cart" size="28" />
            </div>
            <h3>Tu cesta está vacía</h3>
            <p class="muted">Añade productos frescos y de proximidad para recibirlos en Barcelona o recoger gratis en tienda.</p>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary" style="margin-top: 1.25rem;">
                <x-icon name="shopping-bag" size="16" />
                <span>Explorar el catálogo</span>
            </a>
        </div>
    @else
        <div class="two-col">
            <div class="card">
                @foreach($cart['items'] as $productId => $item)
                    @php $product = $item['product']; @endphp
                    <div class="cart-item">
                        <div class="cart-thumb">
                            <x-icon name="shopping-bag" size="24" />
                        </div>
                        <div>
                            <a href="{{ route('catalog.show', $product->slug) }}">
                                <strong style="font-size: 0.98rem; color: var(--slate-900);">{{ $product->name }}</strong>
                            </a>
                            <div class="muted" style="font-size: 0.82rem; margin-top: 2px;">
                                {{ eur($product->price) }} / {{ $product->unitLabel() }}
                            </div>
                            @if(! $item['is_in_stock'])
                                <span class="badge badge-red" style="margin-top: 0.4rem;">
                                    <x-icon name="alert-triangle" size="12" />
                                    <span>Solo {{ qty($item['available_stock'], $product->sale_unit) }} disponibles</span>
                                </span>
                            @endif
                        </div>
                        <div class="cart-qty">
                            <form action="{{ route('cart.update') }}" method="POST" class="inline-form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" 
                                       step="{{ $product->sale_unit == 'kg' ? '0.25' : '1' }}" 
                                       min="{{ $product->sale_unit == 'kg' ? '0.25' : '1' }}" 
                                       onchange="this.form.submit()"
                                       aria-label="Cantidad para {{ $product->name }}">
                            </form>
                            <span class="price" style="min-width: 80px; text-align: right; font-size: 1.05rem;">
                                {{ eur($item['line_total']) }}
                            </span>
                            <form action="{{ route('cart.remove') }}" method="POST" class="inline-form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn btn-sm btn-ghost" style="color: var(--red-600); padding: 0.35rem 0.5rem;" title="Eliminar producto de la cesta">
                                    <x-icon name="trash" size="15" />
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card summary">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Resumen del pedido</h3>
                <div class="summary-row">
                    <span>Subtotal productos:</span>
                    <strong>{{ eur($cart['subtotal']) }}</strong>
                </div>
                <div class="summary-row">
                    <span>Gastos de envío:</span>
                    <span class="muted">{{ $cart['subtotal'] >= config('localmarket.free_delivery_from', 50) ? 'Gratis' : 'Desde ' . eur(config('localmarket.delivery_fee', 4.95)) }}</span>
                </div>
                <div class="summary-row total">
                    <span>Total estimado:</span>
                    <span style="color: var(--emerald-800);">{{ eur($cart['subtotal']) }}</span>
                </div>

                <div style="margin-top: 1.5rem;">
                    @auth
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-block btn-lg" style="font-weight: 700;">
                            <span>Tramitar pedido</span>
                            <x-icon name="arrow-right" size="16" />
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-block btn-lg" style="font-weight: 700;">
                            <x-icon name="user" size="16" />
                            <span>Inicia sesión para tramitar</span>
                        </a>
                        <p class="muted" style="font-size: 0.8rem; text-align: center; margin-top: 0.75rem;">
                            ¿Aún no tienes cuenta? <a href="{{ route('register') }}" style="font-weight: 600;">Regístrate gratis</a>
                        </p>
                    @endauth
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
