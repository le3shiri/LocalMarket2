@extends('layouts.app', ['title' => 'Pago del Pedido ' . $order->number])

@section('content')
<div class="container page" style="max-width: 860px; margin: 2rem auto;">
    <div style="margin-bottom: 2rem; text-align: center;">
        <span class="badge badge-amber" style="font-size: 0.9rem; padding: 0.35rem 0.8rem; margin-bottom: 0.5rem; display: inline-block;">
            Paso 2 de 2: Pago y Confirmación
        </span>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--ink); margin-bottom: 0.5rem;">
            Pasarela de Pago Segura
        </h1>
        <p class="muted">
            Pedido <strong>#{{ $order->number }}</strong> · Total a pagar: <strong style="color: var(--green-700); font-size: 1.3rem;">{{ eur($order->total) }}</strong>
        </p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            {{ session('error') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
        <!-- Payment Methods Card -->
        <div class="card" style="box-shadow: var(--shadow-sm); border: 1px solid var(--border); padding: 1.5rem; border-radius: var(--radius);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                <h2 style="font-size: 1.2rem; font-weight: 700; margin: 0;">💳 Pago con Tarjeta</h2>
                <div style="display: flex; gap: 0.4rem; font-size: 1.25rem;">
                    <span>💳</span>
                    <span>🔒</span>
                </div>
            </div>

            <form method="POST" action="{{ route('payment.mock', $order->id) }}" id="payment-form">
                @csrf
                <div class="field" style="margin-bottom: 1rem;">
                    <label for="card_holder" style="display: block; font-weight: 600; margin-bottom: 0.35rem; font-size: 0.9rem;">
                        Titular de la tarjeta
                    </label>
                    <input type="text" id="card_holder" name="card_holder" value="{{ $order->customer_name }}" required class="form-control" placeholder="Nombre como aparece en la tarjeta">
                </div>

                <div class="field" style="margin-bottom: 1rem;">
                    <label for="card_number" style="display: block; font-weight: 600; margin-bottom: 0.35rem; font-size: 0.9rem;">
                        Número de tarjeta
                    </label>
                    <input type="text" id="card_number" name="card_number" value="4242 •••• •••• 4242" required class="form-control" maxlength="19" placeholder="4242 4242 4242 4242">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="field">
                        <label for="expiry" style="display: block; font-weight: 600; margin-bottom: 0.35rem; font-size: 0.9rem;">Caducidad</label>
                        <input type="text" id="expiry" name="expiry" value="12/28" placeholder="MM/AA" maxlength="5" required class="form-control">
                    </div>
                    <div class="field">
                        <label for="cvv" style="display: block; font-weight: 600; margin-bottom: 0.35rem; font-size: 0.9rem;">CVV / CVC</label>
                        <input type="password" id="cvv" name="cvv" value="123" placeholder="123" maxlength="4" required class="form-control">
                    </div>
                </div>

                <!-- QA Simulation Outcome Selector -->
                <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; padding: 0.85rem; border-radius: 6px; margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #475569; margin-bottom: 0.4rem;">
                        🛠️ Modo de prueba / Simulación académica:
                    </label>
                    <div style="display: flex; gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                            <input type="radio" name="simulate_outcome" value="success" checked>
                            <span style="color: #166534; font-weight: 600;">Simular Pago Exitoso</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                            <input type="radio" name="simulate_outcome" value="failure">
                            <span style="color: #991b1b; font-weight: 600;">Simular Fallo/Rechazo</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center; font-weight: 700; font-size: 1.05rem;">
                    Pagar {{ eur($order->total) }}
                </button>
            </form>

            <div style="margin-top: 1rem; text-align: center; font-size: 0.8rem; color: #64748b;">
                🔒 Transacción encriptada SSL de 256 bits · Certificado LocalMarket Barcelona
            </div>
        </div>

        <!-- Order Summary & Timeout Warning Card -->
        <div>
            <div class="card" style="box-shadow: var(--shadow-sm); border: 1px solid var(--border); padding: 1.5rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Resumen del Pedido</h3>
                
                <div style="max-height: 200px; overflow-y: auto; margin-bottom: 1rem;">
                    @foreach($order->items as $item)
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.5rem;">
                            <span>{{ $item->quantity_formatted }} · {{ $item->product_name }}</span>
                            <strong>{{ eur($item->line_total) }}</strong>
                        </div>
                    @endforeach
                </div>

                <hr style="border: none; border-top: 1px solid var(--border); margin: 0.75rem 0;">

                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.35rem;">
                    <span>Subtotal:</span>
                    <span>{{ eur($order->subtotal) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.35rem;">
                    <span>Coste de entrega:</span>
                    <span>{{ eur($order->delivery_fee) }}</span>
                </div>
                @if($order->discount > 0)
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.35rem; color: #166534;">
                        <span>Descuento aplicado:</span>
                        <span>-{{ eur($order->discount) }}</span>
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800; color: var(--ink); margin-top: 0.5rem; padding-top: 0.5rem; border-top: 2px solid var(--ink);">
                    <span>Total:</span>
                    <span>{{ eur($order->total) }}</span>
                </div>
            </div>

            <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #d97706; padding: 1rem; border-radius: 6px;">
                <strong style="color: #92400e; display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">
                    ⏳ Reserva temporal de existencias
                </strong>
                <p style="font-size: 0.85rem; color: #b45309; line-height: 1.5; margin: 0;">
                    Tus productos están reservados temporalmente durante <strong>30 minutos</strong>. Si el pago no se completa en este plazo, el pedido caducará automáticamente y las existencias volverán al stock disponible.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
