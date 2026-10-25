<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $query = Product::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('q')) {
            $search = '%'.$request->q.'%';
            $query->where('name', 'like', $search);
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('staff.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Product::UNITS;

        return view('staff.products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_unit' => ['required', Rule::in(array_keys(Product::UNITS))],
            'shelf_location' => ['nullable', 'string', 'max:50'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0'],
            'tracks_expiry' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']).'-'.Str::random(5);
        $data['tracks_expiry'] = $request->boolean('tracks_expiry');
        $data['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($data);

        AuditLog::record('product.created', $product, [
            'name' => $product->name,
            'price' => $product->price,
            'sale_unit' => $product->sale_unit,
        ]);

        return redirect()->route('staff.products.index')->with('success', 'Producto registrado correctamente.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $units = Product::UNITS;

        return view('staff.products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_unit' => ['required', Rule::in(array_keys(Product::UNITS))],
            'shelf_location' => ['nullable', 'string', 'max:50'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0'],
            'tracks_expiry' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $data['tracks_expiry'] = $request->boolean('tracks_expiry');
        $data['is_active'] = $request->boolean('is_active');

        $product->update($data);

        AuditLog::record('product.updated', $product, [
            'name' => $product->name,
            'price' => $product->price,
        ]);

        return redirect()->route('staff.products.index')->with('success', 'Producto actualizado con éxito.');
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        AuditLog::record('product.deleted', null, ['name' => $name]);

        return redirect()->route('staff.products.index')->with('success', 'Producto eliminado.');
    }
}
