<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of all products.
     */
    public function index()
    {
        $products = Product::orderBy('name')->get();

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
        ], [
            'sku.unique' => 'This SKU is already in use by another product.',
            'quantity.min' => 'Quantity cannot be negative.',
            'unit_price.min' => 'Unit price cannot be negative.',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product added successfully.');
    }

    /**
     * Display a single product.
     */
     public function show(Request $request, Product $product)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);
        $from = $request->input('from');
        $to   = $request->input('to');
 
        // All transactions, oldest first, so the running balance is computed correctly
        // even when a date filter is applied (the filter only affects what is shown).
        $all = $product->transactions()->with('user')->orderBy('created_at')->orderBy('id')->get();
 
        // Products may have had stock before the first recorded transaction (e.g. seeded data),
        // so the opening balance = current quantity - sum of all transaction changes.
        $opening = $product->quantity - $all->sum('quantity');
 
        $balance = $opening;
        $history = $all->each(function ($t) use (&$balance) {
            $balance += $t->quantity;
            $t->balance_after = $balance;
        });
 
        if ($from) { $history = $history->filter(fn ($t) => $t->created_at->toDateString() >= $from); }
        if ($to)   { $history = $history->filter(fn ($t) => $t->created_at->toDateString() <= $to); }
 
        $history = $history->reverse()->values();   // newest first for display
 
        return view('products.show', compact('product', 'history', 'opening', 'from', 'to'));
    }

    /**
     * Show the form for editing an existing product.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update an existing product in the database.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
        ], [
            'sku.unique' => 'This SKU is already in use by another product.',
            'quantity.min' => 'Quantity cannot be negative.',
            'unit_price.min' => 'Unit price cannot be negative.',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove a product from the database.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}