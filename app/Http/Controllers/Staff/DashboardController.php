<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;

class DashboardController extends Controller
{
    public function index()
    {
        $onlineLocation = Location::where('is_online_source', true)->first();

        // 1. Low stock alerts (at the online fulfillment store)
        $lowStockItems = [];
        if ($onlineLocation) {
            $lowStockItems = StockLevel::where('location_id', $onlineLocation->id)
                ->with('product')
                ->whereHas('product', function ($q) {
                    $q->where('is_active', true);
                })
                ->get()
                ->filter(function (StockLevel $stock) {
                    return $stock->available <= (float) $stock->product->low_stock_threshold;
                });
        }

        // 2. Near expiry alerts (within 3 days)
        $nearExpiryBatches = Batch::with(['product', 'location'])
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<=', now()->addDays(config('localmarket.near_expiry_days', 3)))
            ->orderBy('expiry_date', 'asc')
            ->take(8)
            ->get();

        // 3. System counts
        $totalProducts = Product::where('is_active', true)->count();
        $totalLocations = Location::where('is_active', true)->count();

        return view('staff.dashboard', compact(
            'lowStockItems',
            'nearExpiryBatches',
            'onlineLocation',
            'totalProducts',
            'totalLocations'
        ));
    }
}
