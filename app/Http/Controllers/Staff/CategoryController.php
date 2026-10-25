<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->paginate(15);

        return view('staff.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('staff.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['icon'] = $data['icon'] ?: '🛒';

        $category = Category::create($data);

        AuditLog::record('category.created', $category, ['name' => $category->name]);

        return redirect()->route('staff.categories.index')->with('success', 'Categoría creada con éxito.');
    }

    public function edit(Category $category)
    {
        return view('staff.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['icon'] = $data['icon'] ?: '🛒';

        $category->update($data);

        AuditLog::record('category.updated', $category, ['name' => $category->name]);

        return redirect()->route('staff.categories.index')->with('success', 'Categoría actualizada con éxito.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una categoría que contiene productos asociados.');
        }

        $name = $category->name;
        $category->delete();

        AuditLog::record('category.deleted', null, ['name' => $name]);

        return redirect()->route('staff.categories.index')->with('success', 'Categoría eliminada.');
    }
}
