@extends('layouts.app', ['title' => 'Cesta de la Compra'])

@section('content')
<div class="container page">
    <div class="page-head">
        <h1>Mi cesta de la compra</h1>
        <a href="{{ route('catalog.index') }}" class="btn btn-sm btn-ghost">&larr; Seguir comprando</a>
    </div>

    @if(empty($cart['items']))
        <div class="card empty">
            <div class="empty-icon">🛒</div>
            <h3>Tu cesta está vacía</h3>
            <p class="muted">Añade productos frescos y de proximidad para recibirlos en Barcelona o recoger en tienda.</p>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary" style="margin-top: 1rem;">Explorar el catálogo</a>
        </div>
    @else
        <div class="two-col">
            <div class="card">
                @foreach($cart['items'] as $productId => $item)
                    @php $product = $item['product']; @endphp
                    <div class="cart-item">
                        <div class="cart-thumb">
                            {{ $product->category->icon ?? '🍎' }}
                        </div>
                        <div>
                            <a href="{{ route('catalog.show', $product->slug) }}">
                                <strong>{{ $product->name }}</strong>
                            </a>
                            <div class="muted" style="font-size: .85rem;">
                                {{ eur($product->price) }} / {{ $product->unitLabel() }}
                            </div>
                            @if(! $item['is_in_stock'])
                                <span class="badge badge-red" style="margin-top: .3rem;">
                                    ⚠️ Solo {{ qty($item['available_stock'], $product->sale_unit) }} disponibles
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
                                       onchange="this.form.submit()">
                            </form>
                            <span class="price" style="min-width: 80px; text-align: right;">{{ eur($item['line_total']) }}</span>
                            <form action="{{ route('cart.remove') }}" method="POST" class="inline-form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn btn-sm btn-ghost" style="color: var(--red-600); padding: .3rem .5rem;" title="Eliminar">✕</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card summary">
                <h3>Resumen del pedido</h3>
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
                    <span style="color: var(--green-900);">{{ eur($cart['subtotal']) }}</span>
                </div>

                <div style="margin-top: 1.5rem;">
                    @auth
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-block btn-lg">Tramitar pedido &rarr;</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-block btn-lg">Inicia sesión para tramitar</a>
                        <p class="muted" style="font-size: .8rem; text-align: center; margin-top: .6rem;">
                            ¿Aún no tienes cuenta? <a href="{{ route('register') }}">Regístrate gratis</a>
                        </p>
                    @endauth
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
