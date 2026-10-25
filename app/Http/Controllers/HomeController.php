<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $featuredProducts = Product::where('is_active', true)
            ->whereHas('category', function ($q) {
                $q->where('is_active', true);
            })
            ->with('category')
            ->take(8)
            ->get();

        return view('welcome', compact('categories', 'featuredProducts'));
    }
}
