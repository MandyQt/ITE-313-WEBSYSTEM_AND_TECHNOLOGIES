<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /** Reports page: Stock Movement, Low Stock, Inventory Valuation (server-side HTML). */
    public function index(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $movements = $this->movementQuery($from, $to)->get();
        $lowStock  = $this->lowStockQuery()->get();
        $products  = Product::orderBy('category')->orderBy('name')->get();

        $totalValue = $products->sum(fn ($p) => $p->quantity * $p->unit_price);
        $byCategory = $products->groupBy('category')->map(fn ($items) => [
            'items' => $items->count(),
            'units' => $items->sum('quantity'),
            'value' => $items->sum(fn ($p) => $p->quantity * $p->unit_price),
        ]);

        return view('reports.index', compact('movements', 'lowStock', 'products', 'totalValue', 'byCategory', 'from', 'to'));
    }

    /** CSV export: /reports/export/{type}  (movement | low-stock | valuation) */
    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['movement', 'low-stock', 'valuation']), 404);

        [$from, $to] = $this->dateRange($request);
        $filename = "{$type}-report-" . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($type, $from, $to) {
            $out = fopen('php://output', 'w');

            if ($type === 'movement') {
                fputcsv($out, ['Date/Time', 'Product', 'SKU', 'Type', 'Quantity', 'Reference', 'User', 'Notes']);
                foreach ($this->movementQuery($from, $to)->get() as $t) {
                    fputcsv($out, [
                        $t->created_at->format('Y-m-d H:i:s'), $t->product->name ?? '', $t->product->sku ?? '',
                        $t->type, $t->quantity, $t->reference_number, $t->user->name ?? '', $t->notes,
                    ]);
                }
            } elseif ($type === 'low-stock') {
                fputcsv($out, ['SKU', 'Product', 'Category', 'Quantity', 'Reorder Level', 'Status', 'Supplier']);
                foreach ($this->lowStockQuery()->get() as $p) {
                    fputcsv($out, [$p->sku, $p->name, $p->category, $p->quantity, $p->reorder_level, $p->stock_status, $p->supplier]);
                }
            } else {
                fputcsv($out, ['SKU', 'Product', 'Category', 'Quantity', 'Unit Price', 'Total Value']);
                $total = 0;
                foreach (Product::orderBy('category')->orderBy('name')->get() as $p) {
                    $value = $p->quantity * $p->unit_price;
                    $total += $value;
                    fputcsv($out, [$p->sku, $p->name, $p->category, $p->quantity, number_format($p->unit_price, 2, '.', ''), number_format($value, 2, '.', '')]);
                }
                fputcsv($out, ['', '', '', '', 'TOTAL', number_format($total, 2, '.', '')]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // ---------- helpers ----------

    private function dateRange(Request $request): array
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);

        return [
            $request->input('from', now()->startOfMonth()->toDateString()),
            $request->input('to', now()->toDateString()),
        ];
    }

    private function movementQuery(string $from, string $to)
    {
        return InventoryTransaction::with(['product', 'user'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->recent();
    }

    private function lowStockQuery()
    {
        // Includes out-of-stock items (quantity <= reorder_level), worst first.
        return Product::whereColumn('quantity', '<=', 'reorder_level')->orderBy('quantity');
    }
}
