<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $locations = Location::where('is_active', true)->get();
        $selectedLocation = $request->filled('location_id')
            ? $locations->firstWhere('id', $request->location_id)
            : $locations->first();

        $stockLevels = [];
        if ($selectedLocation) {
            $stockLevels = StockLevel::where('location_id', $selectedLocation->id)
                ->with('product.category')
                ->join('products', 'stock_levels.product_id', '=', 'products.id')
                ->orderBy('products.name')
                ->select('stock_levels.*')
                ->paginate(15)
                ->withQueryString();
        }

        return view('staff.inventory.index', compact('locations', 'selectedLocation', 'stockLevels'));
    }

    public function movements(Request $request)
    {
        $locations = Location::where('is_active', true)->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        $query = StockMovement::with(['product', 'location', 'user'])->latest();

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $movements = $query->paginate(20)->withQueryString();

        return view('staff.inventory.movements', compact('movements', 'locations', 'products'));
    }

    public function createReceipt()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->get();

        return view('staff.inventory.receipt', compact('products', 'locations'));
    }

    public function storeReceipt(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', 'max:255'],
            'lot_code' => ['nullable', 'string', 'max:50'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $location = Location::findOrFail($data['location_id']);

        try {
            $this->inventoryService->recordReceipt(
                $product,
                $location,
                (float) $data['quantity'],
                auth()->user(),
                $data['reason'],
                $data['lot_code'] ?? null,
                $data['expiry_date'] ?? null
            );

            return redirect()->route('staff.inventory.index', ['location_id' => $location->id])
                ->with('success', 'Entrada de mercancía registrada correctamente.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function createTransfer()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->get();

        return view('staff.inventory.transfer', compact('products', 'locations'));
    }

    public function storeTransfer(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'from_location_id' => ['required', 'exists:locations,id'],
            'to_location_id' => ['required', 'exists:locations,id', 'different:from_location_id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $from = Location::findOrFail($data['from_location_id']);
        $to = Location::findOrFail($data['to_location_id']);

        try {
            $this->inventoryService->recordTransfer(
                $product,
                $from,
                $to,
                (float) $data['quantity'],
                auth()->user(),
                $data['reason']
            );

            return redirect()->route('staff.inventory.index', ['location_id' => $to->id])
                ->with('success', 'Traspaso de mercancía completado con éxito.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function createAdjustment()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->get();

        return view('staff.inventory.adjustment', compact('products', 'locations'));
    }

    public function storeAdjustment(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'quantity_delta' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $location = Location::findOrFail($data['location_id']);

        try {
            $this->inventoryService->recordAdjustment(
                $product,
                $location,
                (float) $data['quantity_delta'],
                auth()->user(),
                $data['reason']
            );

            return redirect()->route('staff.inventory.index', ['location_id' => $location->id])
                ->with('success', 'Ajuste de inventario aplicado correctamente.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function batches(Request $request)
    {
        $locations = Location::where('is_active', true)->get();

        $query = Batch::with(['product', 'location'])
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc');

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $batches = $query->paginate(20)->withQueryString();

        return view('staff.inventory.batches', compact('batches', 'locations'));
    }
}
