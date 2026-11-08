<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryTransactionResource;
use App\Http\Resources\ProductResource;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    /**
     * GET /api/dashboard/stats
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => [
                'total_products'      => Product::count(),
                'low_stock_count'     => Product::lowStock()->count(),
                'out_of_stock_count'  => Product::outOfStock()->count(),
                'total_value'         => (float) Product::sum(DB::raw('quantity * unit_price')),
                'transactions_today'  => InventoryTransaction::whereDate('created_at', today())->count(),
                'unread_notifications'=> auth()->user()->unreadNotifications()->count(),
            ],
            'meta' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/products/low-stock
     * Includes out-of-stock items too (quantity <= reorder_level), worst first.
     */
    public function lowStock()
    {
        $products = Product::whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->get();

        return ProductResource::collection($products)->additional([
            'meta' => ['count' => $products->count(), 'generated_at' => now()->toIso8601String()],
        ]);
    }

    /**
     * GET /api/transactions/recent
     */
    public function recentTransactions()
    {
        $transactions = InventoryTransaction::with(['product', 'user'])
            ->recent()
            ->take(10)
            ->get();

        return InventoryTransactionResource::collection($transactions)->additional([
            'meta' => ['generated_at' => now()->toIso8601String()],
        ]);
    }
}
