@extends('layouts.app', ['title' => 'Tramitar Pedido'])

@section('content')
<div class="container page">
    <h1>Finalizar compra</h1>
    <p class="muted">Selecciona tu método de entrega y completa tus datos para reservar tus productos.</p>

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
                                <strong>🚚 Entrega a domicilio</strong>
                                <small>Reparto en Barcelona ciudad (08001–08042). Gratis a partir de {{ eur($freeFrom) }}.</small>
                            </label>
                        </div>
                        <div class="option-card">
                            <input type="radio" id="method-pickup" name="fulfilment_method" value="pickup" {{ old('fulfilment_method') === 'pickup' ? 'checked' : '' }}>
                            <label for="method-pickup">
                                <strong>🏬 Recogida en tienda</strong>
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
                <h3>Resumen de la compra</h3>

                <div style="margin-bottom: 1rem; max-height: 220px; overflow-y: auto;">
                    @foreach($cart['items'] as $item)
                        <div class="summary-row" style="font-size: .88rem;">
                            <span>{{ qty($item['quantity'], $item['product']->sale_unit) }} · {{ $item['product']->name }}</span>
                            <span>{{ eur($item['line_total']) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong>{{ eur($cart['subtotal']) }}</strong>
                </div>

                <div class="summary-row">
                    <span>Envío:</span>
                    <span>
                        @if($cart['subtotal'] >= $freeFrom)
                            <strong style="color: var(--green-700);">Gratis</strong>
                        @else
                            {{ eur($deliveryFee) }} <small class="muted">(gratis desde {{ eur($freeFrom) }})</small>
                        @endif
                    </span>
                </div>

                <div class="summary-row total">
                    <span>Total a pagar:</span>
                    <span style="color: var(--green-950);">
                        {{ eur($cart['subtotal'] >= $freeFrom ? $cart['subtotal'] : $cart['subtotal'] + $deliveryFee) }}
                    </span>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.5rem;">
                    Confirmar pedido y reservar stock
                </button>

                <p class="muted" style="font-size: .78rem; text-align: center; margin-top: .8rem;">
                    🔒 Al confirmar, se reservará el stock de tus productos de forma inmediata para evitar que se agoten.
                </p>
            </div>
        </div>
    </form>
</div>
@endsection
