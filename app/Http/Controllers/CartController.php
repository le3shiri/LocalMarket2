<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index()
    {
        $cart = $this->cartService->getCart();

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        $product = Product::where('id', $data['product_id'])
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $this->cartService->add($product, (float) $data['quantity']);

            return back()->with('success', 'Añadido a la cesta: '.$product->name);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        try {
            $this->cartService->update($product, (float) $data['quantity']);

            return back()->with('success', 'Cesta actualizada.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $this->cartService->remove($data['product_id']);

        return back()->with('success', 'Producto eliminado de la cesta.');
    }
}
