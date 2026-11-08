@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h2>{{ $product->name }}</h2>
        <div>
            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">Edit</a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back to List</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">SKU</dt>
                <dd class="col-sm-9">{{ $product->sku }}</dd>

                <dt class="col-sm-3">Category</dt>
                <dd class="col-sm-9">{{ $product->category }}</dd>

                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $product->description ?? '—' }}</dd>

                <dt class="col-sm-3">Quantity</dt>
                <dd class="col-sm-9">{{ $product->quantity }}</dd>

                <dt class="col-sm-3">Reorder Level</dt>
                <dd class="col-sm-9">{{ $product->reorder_level }}</dd>

                <dt class="col-sm-3">Unit Price</dt>
                <dd class="col-sm-9">${{ number_format($product->unit_price, 2) }}</dd>

                <dt class="col-sm-3">Total Value</dt>
                <dd class="col-sm-9">${{ number_format($product->total_value, 2) }}</dd>

                <dt class="col-sm-3">Supplier</dt>
                <dd class="col-sm-9">{{ $product->supplier ?? '—' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    @if ($product->isOutOfStock())
                        <span class="badge bg-danger">Out of Stock</span>
                    @elseif ($product->isLowStock())
                        <span class="badge bg-warning text-dark">Low Stock</span>
                    @else
                        <span class="badge bg-success">In Stock</span>
                    @endif
                </dd>
            </dl>
        </div>
    </div>
      @include('products._transaction_history')
@endsection