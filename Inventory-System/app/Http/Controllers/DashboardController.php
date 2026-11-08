<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::outOfStock()->count();

        $totalValue = Product::sum(DB::raw('quantity * unit_price'));

        $recentProducts = Product::latest()->take(5)->get();

        $categorySummary = Product::selectRaw('category, SUM(quantity * unit_price) as total_value, SUM(quantity) as total_quantity')
            ->groupBy('category')
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'totalValue',
            'recentProducts',
            'categorySummary'
        ));
    }
}