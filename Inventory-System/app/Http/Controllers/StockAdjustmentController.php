<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    /** Reasons offered in the dropdown (key => label). */
    public const REASONS = [
        'physical_count' => 'Physical count discrepancy',
        'damaged'        => 'Damaged goods',
        'theft_loss'     => 'Theft / loss',
        'system_error'   => 'System error',
        'other'          => 'Other',
    ];

    /** Only admins ("authorized users") may adjust stock. */
    private function authorizeAdjustment(): void
    {
        abort_unless(auth()->user()->is_admin, 403, 'Only administrators can adjust stock.');
    }

    /**
     * Step 2: show the adjustment form.
     */
    public function create()
    {
        $this->authorizeAdjustment();

        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'quantity']);
        $reasons  = self::REASONS;

        return view('adjustments.create', compact('products', 'reasons'));
    }

    /**
     * Step 3: process the adjustment.
     *  1. Calculate difference (physical - system)
     *  2. Create an 'adjustment' transaction holding that signed difference
     *  3. The observer applies it to products.quantity (and checks low stock)
     *  4. Reason + user are logged on the transaction (notes / user_id)
     */
    public function store(Request $request)
    {
        $this->authorizeAdjustment();

        $validated = $request->validate([
            'product_id'     => 'required|exists:products,id',
            'physical_count' => 'required|integer|min:0',
            'reason'         => 'required|in:' . implode(',', array_keys(self::REASONS)),
            'notes'          => 'nullable|string|max:1000',
        ]);

        $result = DB::transaction(function () use ($validated) {
            // Lock the row so the "system quantity" can't change between
            // reading it here and the observer updating it.
            $product    = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $difference = $validated['physical_count'] - $product->quantity;

            if ($difference === 0) {
                return ['product' => $product, 'difference' => 0];
            }

            $reasonLabel = self::REASONS[$validated['reason']];
            $notes = "Reason: {$reasonLabel}. System: {$product->quantity}, physical count: {$validated['physical_count']}."
                   . (filled($validated['notes'] ?? null) ? " {$validated['notes']}" : '');

            InventoryTransaction::create([
                'product_id'       => $product->id,
                'user_id'          => auth()->id(),
                'type'             => 'adjustment',
                'quantity'         => $difference,   // signed: + found extra, - missing
                'reference_number' => 'ADJ-' . now()->format('Ymd-His'),
                'notes'            => $notes,
            ]);

            return ['product' => $product, 'difference' => $difference];
        });

        if ($result['difference'] === 0) {
            return back()
                ->withErrors(['physical_count' => 'Physical count matches the system quantity — no adjustment needed.'])
                ->withInput();
        }

        $sign = $result['difference'] > 0 ? '+' : '';

        return redirect()
            ->route('transactions.index')
            ->with('success', "Stock adjusted for \"{$result['product']->name}\" ({$sign}{$result['difference']}).");
    }

    /**
     * AJAX: current system quantity for the selected product.
     * GET /api/products/{product}/quantity
     */
    public function quantity(Product $product): JsonResponse
    {
        return response()->json([
            'data' => [
                'id'       => $product->id,
                'name'     => $product->name,
                'sku'      => $product->sku,
                'quantity' => $product->quantity,
            ],
        ]);
    }
}
