<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;

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
            ->orderBy('name')
            ->get();

        return view('staff.pos.create', compact('storeLocations', 'products'));
    }

    public function store(Request $request)
    {
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
