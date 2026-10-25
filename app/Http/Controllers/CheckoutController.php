<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {}

    public function index()
    {
        $cart = $this->cartService->getCart();

        if (empty($cart['items'])) {
            return redirect()->route('cart.index')->with('error', 'Tu cesta está vacía.');
        }

        $user = auth()->user();
        $deliveryFee = (float) config('localmarket.delivery_fee', 4.95);
        $freeFrom = (float) config('localmarket.free_delivery_from', 50.0);

        return view('checkout.index', compact('cart', 'user', 'deliveryFee', 'freeFrom'));
    }

    public function store(Request $request)
    {
        $allowedPostcodes = config('localmarket.delivery_postcodes', []);

        $data = $request->validate([
            'fulfilment_method' => ['required', 'in:delivery,pickup'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required_if:fulfilment_method,delivery', 'nullable', 'string', 'max:255'],
            'postcode' => [
                'required_if:fulfilment_method,delivery',
                'nullable',
                'string',
                'max:10',
                function ($attribute, $value, $fail) use ($request, $allowedPostcodes) {
                    if ($request->input('fulfilment_method') === 'delivery') {
                        if (! in_array(trim((string) $value), $allowedPostcodes, true)) {
                            $fail('El código postal no pertenece al área de reparto en Barcelona ciudad (08001–08042).');
                        }
                    }
                },
            ],
            'city' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $order = $this->orderService->createOrder(auth()->user(), $data);

            // In Sprint 4/5, redirects to the payment simulation/gateway
            return redirect()->route('account.orders.show', $order->id)
                ->with('success', '¡Pedido registrado con éxito! Número: '.$order->number.'. Stock reservado.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
