<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    /**
     * Display a paginated, filterable list of all transactions.
     */
    public function index(Request $request)
    {
        $query = InventoryTransaction::with(['product', 'user'])->recent();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20)->withQueryString();

        $products = Product::orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'products'));
    }

    /**
     * Show the form for recording a new transaction.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();

        $types = [
            'stock_in' => 'Stock In',
            'stock_out' => 'Stock Out',
            'adjustment' => 'Adjustment',
        ];

        return view('transactions.create', compact('products', 'types'));
    }

    /**
     * Validate and store a new transaction.
     *
     * The 'quantity' field is always entered as a positive number by the
     * user (except adjustments, which may be signed). It's converted here
     * into the signed value actually stored, based on transaction type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:stock_in,stock_out,adjustment',
            'quantity' => 'required|numeric',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $signedQuantity = match ($validated['type']) {
            'stock_in' => abs($validated['quantity']),
            'stock_out' => -abs($validated['quantity']),
            'adjustment' => $validated['quantity'],
        };

        // Custom validation (answers the lab's Analysis Question): rather than
        // a separate Rule class, this is done as a manual check immediately
        // after the base validation passes — functionally the same as an
        // "after" validation hook, just inline since it only applies to one field.
        if ($validated['type'] === 'stock_out' && abs($signedQuantity) > $product->quantity) {
            return back()
                ->withErrors(['quantity' => "Insufficient stock. Only {$product->quantity} units of \"{$product->name}\" are available."])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $signedQuantity) {
            // Creating this record fires InventoryTransactionObserver::created(),
            // which applies the stock change to the product automatically.
            InventoryTransaction::create([
                'product_id' => $validated['product_id'],
                'user_id' => auth()->id(),
                'type' => $validated['type'],
                'quantity' => $signedQuantity,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction recorded successfully.');
    }

    /**
     * Display a single transaction's full details.
     */
    public function show(InventoryTransaction $transaction)
    {
        $transaction->load(['product', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Delete a transaction (admin only), reversing its effect on stock.
     */
    public function destroy(InventoryTransaction $transaction)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Only administrators can delete transactions.');
        }

        DB::transaction(function () use ($transaction) {
            $product = Product::lockForUpdate()->find($transaction->product_id);

            // Reverse this transaction's original effect on stock.
            // (Design choice, per the lab's prompt: yes, deleting should
            // reverse the stock change — otherwise the product's quantity
            // would silently drift out of sync with its transaction history.)
            $reversedQuantity = $product->quantity - $transaction->quantity;

            if ($reversedQuantity < 0) {
                abort(422, 'Cannot delete this transaction: reversing it would result in negative inventory.');
            }

            $product->quantity = $reversedQuantity;
            $product->save();

            $transaction->delete();
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted and stock reversed.');
    }
}