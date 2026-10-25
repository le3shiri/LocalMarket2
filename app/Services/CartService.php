<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;

class CartService
{
    public const SESSION_KEY = 'cart';

    /**
     * Get the current cart from session with loaded Product models.
     */
    public function getCart(): array
    {
        $rawCart = session(self::SESSION_KEY, []);
        $items = [];
        $subtotal = 0.0;

        if (empty($rawCart)) {
            return ['items' => [], 'subtotal' => 0.0, 'count' => 0];
        }

        $products = Product::whereIn('id', array_keys($rawCart))
            ->where('is_active', true)
            ->with('category')
            ->get()
            ->keyBy('id');

        $onlineLocation = Location::where('is_online_source', true)->first();

        foreach ($rawCart as $productId => $quantity) {
            $product = $products->get($productId);
            if (! $product) {
                continue;
            }

            $available = 0.0;
            if ($onlineLocation) {
                $stock = StockLevel::where('product_id', $product->id)
                    ->where('location_id', $onlineLocation->id)
                    ->first();
                $available = $stock ? $stock->available : 0.0;
            }

            $lineTotal = round((float) $product->price * (float) $quantity, 2);
            $subtotal += $lineTotal;

            $items[$productId] = [
                'product' => $product,
                'quantity' => (float) $quantity,
                'line_total' => $lineTotal,
                'available_stock' => $available,
                'is_in_stock' => $available >= $quantity,
            ];
        }

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'count' => count($items),
        ];
    }

    /**
     * Add product to cart with sale unit quantity rules (unit/pack = int, kg = steps of 0.25).
     */
    public function add(Product $product, float $quantity): void
    {
        $onlineLocation = Location::where('is_online_source', true)->first();
        if ($onlineLocation) {
            $stock = StockLevel::where('product_id', $product->id)
                ->where('location_id', $onlineLocation->id)
                ->first();
            $available = $stock ? (float) $stock->available : 0.0;
        } else {
            $available = 0.0;
        }

        $cart = session(self::SESSION_KEY, []);
        $current = $cart[$product->id] ?? 0.0;
        $newQuantity = $current + $quantity;

        $newQuantity = $this->sanitizeQuantity($product, $newQuantity);

        if ($newQuantity > $available) {
            throw new \InvalidArgumentException('No hay suficiente stock disponible para '.$product->name.' (máximo '.$available.').');
        }

        $cart[$product->id] = $newQuantity;
        session([self::SESSION_KEY => $cart]);
    }

    /**
     * Update quantity for a product in cart.
     */
    public function update(Product $product, float $quantity): void
    {
        $cart = session(self::SESSION_KEY, []);

        if ($quantity <= 0) {
            unset($cart[$product->id]);
        } else {
            $onlineLocation = Location::where('is_online_source', true)->first();
            if ($onlineLocation) {
                $stock = StockLevel::where('product_id', $product->id)
                    ->where('location_id', $onlineLocation->id)
                    ->first();
                $available = $stock ? (float) $stock->available : 0.0;
            } else {
                $available = 0.0;
            }

            $sanitized = $this->sanitizeQuantity($product, $quantity);
            if ($sanitized > $available) {
                throw new \InvalidArgumentException('No hay suficiente stock disponible para '.$product->name.' (máximo '.$available.').');
            }

            $cart[$product->id] = $sanitized;
        }

        session([self::SESSION_KEY => $cart]);
    }

    /**
     * Remove product from cart.
     */
    public function remove(int $productId): void
    {
        $cart = session(self::SESSION_KEY, []);
        unset($cart[$productId]);
        session([self::SESSION_KEY => $cart]);
    }

    /**
     * Clear the whole cart.
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Sanitize quantity based on sale unit.
     */
    protected function sanitizeQuantity(Product $product, float $quantity): float
    {
        if ($product->sale_unit === Product::UNIT_KG) {
            // Steps of 0.25 kg (AS-05)
            $steps = round($quantity / 0.25);

            return max(0.25, $steps * 0.25);
        }

        // unit or pack: strictly whole numbers
        return (float) max(1, (int) round($quantity));
    }
}
