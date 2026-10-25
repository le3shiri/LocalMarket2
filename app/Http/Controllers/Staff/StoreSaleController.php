<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreSaleController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function create()
    {
        $storeLocations = Location::where('type', Location::TYPE_STORE)
            ->where('is_active', true)
            ->get();

        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Load current store stock levels so POS displays accurate real-time availability
        $stockLevels = StockLevel::whereIn('location_id', $storeLocations->pluck('id'))
            ->get()
            ->groupBy('location_id')
            ->map(function ($rows) {
                return $rows->keyBy('product_id')->map(function ($st) {
                    return (float) $st->available;
                });
            });

        return view('staff.pos.create', compact('storeLocations', 'products', 'categories', 'stockLevels'));
    }

    public function store(Request $request)
    {
        // Support multi-item POS basket
        if ($request->has('items') && is_array($request->items)) {
            $data = $request->validate([
                'location_id' => ['required', 'exists:locations,id'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'exists:products,id'],
                'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
                'payment_method' => ['nullable', 'string', 'in:cash,card'],
                'tendered_amount' => ['nullable', 'numeric'],
            ]);

            $location = Location::findOrFail($data['location_id']);
            $totalCharged = 0.0;
            $itemsCount = 0;

            try {
                DB::transaction(function () use ($data, $location, &$totalCharged, &$itemsCount) {
                    foreach ($data['items'] as $itemData) {
                        $product = Product::findOrFail($itemData['product_id']);
                        $qty = (float) $itemData['quantity'];
                        $this->inventoryService->recordStoreSale(
                            $product,
                            $location,
                            $qty,
                            auth()->user()
                        );
                        $totalCharged += ($product->price * $qty);
                        $itemsCount++;
                    }
                });

                $methodLabel = ($data['payment_method'] ?? 'card') === 'cash' ? 'efectivo' : 'tarjeta';

                return redirect()->route('staff.pos.create')
                    ->with('success', "Venta completada en {$location->name}: {$itemsCount} líneas registradas (".eur($totalCharged)." cobrados con {$methodLabel}). Stock actualizado en tiempo real.");
            } catch (Exception $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        }

        // Backward-compatible single item sale (used by existing tests)
        $data = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        $location = Location::findOrFail($data['location_id']);
        $product = Product::findOrFail($data['product_id']);

        try {
            $this->inventoryService->recordStoreSale(
                $product,
                $location,
                (float) $data['quantity'],
                auth()->user()
            );

            return redirect()->route('staff.pos.create')
                ->with('success', 'Venta registrada: '.qty($data['quantity'], $product->sale_unit).' de '.$product->name.'. Stock de tienda actualizado.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
