@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h2>Transaction #{{ $transaction->id }}</h2>
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Transaction Details</h6></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Date/Time</dt>
                        <dd class="col-sm-8">{{ $transaction->created_at->format('M d, Y h:i A') }}</dd>

                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">
                            @php
                                $typeBadge = match($transaction->type) {
                                    'stock_in' => 'bg-success',
                                    'stock_out' => 'bg-danger',
                                    'adjustment' => 'bg-info text-dark',
                                };
                            @endphp
                            <span class="badge {{ $typeBadge }}">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</span>
                        </dd>

                        <dt class="col-sm-4">Quantity</dt>
                        <dd class="col-sm-8 {{ $transaction->quantity >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                            {{ $transaction->quantity >= 0 ? '+' : '' }}{{ $transaction->quantity }}
                        </dd>

                        <dt class="col-sm-4">Reference #</dt>
                        <dd class="col-sm-8">{{ $transaction->reference_number ?? '—' }}</dd>

                        <dt class="col-sm-4">Notes</dt>
                        <dd class="col-sm-8">{{ $transaction->notes ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Product</h6></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Name</dt>
                        <dd class="col-sm-8"><a href="{{ route('products.show', $transaction->product) }}">{{ $transaction->product->name }}</a></dd>

                        <dt class="col-sm-4">SKU</dt>
                        <dd class="col-sm-8">{{ $transaction->product->sku }}</dd>

                        <dt class="col-sm-4">Current Stock</dt>
                        <dd class="col-sm-8">{{ $transaction->product->quantity }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card h-100 mt-3">
                <div class="card-header"><h6 class="mb-0">Performed By</h6></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">User</dt>
                        <dd class="col-sm-8">{{ $transaction->user->name ?? 'Unknown' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->is_admin)
        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Delete this transaction? This will reverse its effect on stock.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-trash me-1"></i>Delete Transaction
            </button>
        </form>
    @endif
@endsection