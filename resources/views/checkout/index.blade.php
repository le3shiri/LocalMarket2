@extends('layouts.app', ['title' => 'Tramitar Pedido'])

@section('content')
<div class="container page">
    <div class="page-head">
        <div>
            <h1>Finalizar compra</h1>
            <p class="muted">Selecciona tu método de entrega y completa tus datos para reservar tus productos de forma inmediata.</p>
        </div>
        <a href="{{ route('cart.index') }}" class="btn btn-sm btn-secondary">
            <x-icon name="arrow-left" size="14" />
            <span>Volver a la cesta</span>
        </a>
    </div>

    <form action="{{ route('checkout.store') }}" method="POST" data-once>
        @csrf

        <div class="two-col">
            <div>
                <!-- Method Selection -->
                <div class="card">
                    <div class="card-title">
                        <h2>1. Método de entrega</h2>
                    </div>

                    <div class="option-cards">
                        <div class="option-card">
                            <input type="radio" id="method-delivery" name="fulfilment_method" value="delivery" {{ old('fulfilment_method', 'delivery') === 'delivery' ? 'checked' : '' }}>
                            <label for="method-delivery">
                                <strong>
                                    <x-icon name="truck" size="18" />
                                    <span>Entrega a domicilio</span>
                                </strong>
                                <small>Reparto en Barcelona ciudad (08001–08042). Gratis a partir de {{ eur($freeFrom) }}.</small>
                            </label>
                        </div>
                        <div class="option-card">
                            <input type="radio" id="method-pickup" name="fulfilment_method" value="pickup" {{ old('fulfilment_method') === 'pickup' ? 'checked' : '' }}>
                            <label for="method-pickup">
                                <strong>
                                    <x-icon name="store" size="18" />
                                    <span>Recogida en tienda</span>
                                </strong>
                                <small>Gratis. Carrer del Mercat, 12, Ciutat Vella, 08001 Barcelona.</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Contact & Address -->
                <div class="card">
                    <div class="card-title">
                        <h2>2. Datos de contacto y entrega</h2>
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="customer_name">Nombre y apellidos *</label>
                            <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $user->name) }}" required>
                            @error('customer_name') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label for="phone">Teléfono de contacto *</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required placeholder="612 34 56 78">
                            @error('phone') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field field-full">
                            <label for="customer_email">Correo electrónico para confirmación *</label>
                            <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email', $user->email) }}" required>
                            @error('customer_email') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div id="delivery-fields" style="margin-top: 1rem;">
                        <div class="form-grid">
                            <div class="field field-full">
                                <label for="address">Dirección de entrega (calle, número, piso/puerta) *</label>
                                <input type="text" id="address" name="address" value="{{ old('address') }}" placeholder="Carrer d'Aragó 142, 3r 2a">
                                @error('address') <span class="error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label for="postcode">Código Postal (Barcelona 08001–08042) *</label>
                                <input type="text" id="postcode" name="postcode" value="{{ old('postcode', '08001') }}" maxlength="5" placeholder="08001">
                                @error('postcode') <span class="error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label for="city">Ciudad</label>
                                <input type="text" id="city" name="city" value="Barcelona" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="field" style="margin-top: 1rem;">
                        <label for="notes">Instrucciones para la entrega o preparación (opcional)</label>
                        <textarea id="notes" name="notes" placeholder="Ej: Llamar al timbre 3A, dejar en portería si no estoy...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sticky -->
            <div class="card summary">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Resumen de la compra</h3>

                <div style="margin-bottom: 1rem; max-height: 220px; overflow-y: auto;">
                    @foreach($cart['items'] as $item)
                        <div class="summary-row" style="font-size: 0.88rem;">
                            <span>{{ qty($item['quantity'], $item['product']->sale_unit) }} · {{ $item['product']->name }}</span>
                            <span style="font-weight: 600;">{{ eur($item['line_total']) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong>{{ eur($cart['subtotal']) }}</strong>
                </div>

                @if(isset($discount) && $discount > 0)
                    <div class="summary-row" style="color: var(--emerald-700);">
                        <span>Descuento ({{ $coupon->code }}):</span>
                        <strong>-{{ eur($discount) }}</strong>
                    </div>
                @endif

                <div class="summary-row">
                    <span>Envío:</span>
                    <span>
                        @if($cart['subtotal'] >= $freeFrom)
                            <strong style="color: var(--emerald-700);">Gratis</strong>
                        @else
                            {{ eur($deliveryFee) }} <small class="muted">(gratis desde {{ eur($freeFrom) }})</small>
                        @endif
                    </span>
                </div>

                @php
                    $finalSub = max(0.00, $cart['subtotal'] - ($discount ?? 0));
                    $finalTotal = $finalSub + ($cart['subtotal'] >= $freeFrom ? 0 : $deliveryFee);
                @endphp

                <div class="summary-row total">
                    <span>Total a pagar:</span>
                    <span style="color: var(--slate-900);">
                        {{ eur($finalTotal) }}
                    </span>
                </div>

                <!-- Coupon Box -->
                <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px dashed var(--border-light);">
                    @if(isset($coupon) && $coupon)
                        <div style="display: flex; justify-content: space-between; align-items: center; background: var(--emerald-50); border: 1px solid var(--emerald-200); padding: 0.6rem 0.85rem; border-radius: var(--radius-sm);">
                            <span style="font-size: 0.85rem; color: var(--emerald-800); font-weight: 600; display: flex; align-items: center; gap: 0.4rem;">
                                <x-icon name="ticket" size="16" />
                                <span>Cupón <strong>{{ $coupon->code }}</strong> (-{{ $coupon->valueFormatted() }})</span>
                            </span>
                            <form action="{{ route('checkout.coupon.remove') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" style="background: none; border: none; color: var(--red-600); cursor: pointer; font-size: 0.8rem; text-decoration: underline; font-weight: 500;">
                                    Quitar
                                </button>
                            </form>
                        </div>
                    @else
                        <div style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--slate-800);">¿Tienes un cupón de descuento?</div>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" form="coupon-form" name="code" placeholder="CÓDIGO" style="text-transform: uppercase; font-size: 0.85rem; padding: 0.4rem 0.6rem; flex: 1; border: 1px solid var(--border-light); border-radius: var(--radius-sm);">
                            <button type="submit" form="coupon-form" class="btn btn-sm btn-outline">Aplicar</button>
                        </div>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.5rem; font-weight: 700;">
                    <x-icon name="check" size="18" stroke="2.5" />
                    <span>Confirmar pedido y reservar stock</span>
                </button>

                <p class="muted" style="font-size: 0.78rem; text-align: center; margin-top: 0.8rem; display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                    <x-icon name="lock" size="13" />
                    <span>Reserva de stock inmediata garantizada.</span>
                </p>
            </div>
        </div>
    </form>

    <form id="coupon-form" action="{{ route('checkout.coupon.apply') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>
@endsection
