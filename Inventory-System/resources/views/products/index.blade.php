@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Product Inventory</h3>
        <a href="{{ route('products.create') }}" class="btn btn-danger">
            <i class="bi bi-plus-circle me-1"></i> Add Product
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $product->sku }}</td>
                                <td>
                                    <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none text-dark fw-semibold">
                                        {{ $product->name }}
                                    </a>
                                </td>
                                <td><span class="badge bg-secondary">{{ $product->category }}</span></td>
                                <td>{{ $product->quantity }}</td>
                                <td>${{ number_format($product->unit_price, 2) }}</td>
                                <td>
                                    @if($product->quantity <= 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($product->isLowStock())
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-outline-secondary me-1">View</a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection