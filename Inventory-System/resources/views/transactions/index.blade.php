@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <h2>Inventory Transactions</h2>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Record Transaction
        </a>
    </div>

    <!-- Filter form -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('transactions.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Product</label>
                    <select name="product_id" class="form-select form-select-sm">
                        <option value="">All Products</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="stock_in" {{ request('type') == 'stock_in' ? 'selected' : '' }}>Stock In</option>
                        <option value="stock_out" {{ request('type') == 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                        <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reference #</th>
                            <th>User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <a href="{{ route('products.show', $transaction->product) }}">
                                        {{ $transaction->product->name }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $typeBadge = match($transaction->type) {
                                            'stock_in' => 'bg-success',
                                            'stock_out' => 'bg-danger',
                                            'adjustment' => 'bg-info text-dark',
                                        };
                                        $typeLabel = match($transaction->type) {
                                            'stock_in' => 'Stock In',
                                            'stock_out' => 'Stock Out',
                                            'adjustment' => 'Adjustment',
                                        };
                                    @endphp
                                    <span class="badge {{ $typeBadge }}">{{ $typeLabel }}</span>
                                </td>
                                <td class="{{ $transaction->quantity >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                    {{ $transaction->quantity >= 0 ? '+' : '' }}{{ $transaction->quantity }}
                                </td>
                                <td>{{ $transaction->reference_number ?? '—' }}</td>
                                <td>{{ $transaction->user->name ?? 'Unknown' }}</td>
                                <td>
                                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if (auth()->user()->is_admin)
                                        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this transaction? This will reverse its effect on stock.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection