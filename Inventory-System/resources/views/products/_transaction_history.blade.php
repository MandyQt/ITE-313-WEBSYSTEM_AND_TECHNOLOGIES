{{-- Part 9 Step 1. Include at the bottom of products/show.blade.php:
     @include('products._transaction_history')
     Needs $product, $history, $opening, $from, $to from ProductController@show. --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-bold">Transaction History</span>
            <form method="GET" action="{{ route('products.show', $product) }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small mb-0">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-0">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <button class="btn btn-danger btn-sm">Filter</button>
                    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle mb-0">
            <thead class="table-light"><tr>
                <th class="ps-3">Date/Time</th><th>Type</th><th class="text-end">Change</th>
                <th class="text-end">Balance After</th><th>Reference</th><th class="pe-3">User</th>
            </tr></thead>
            <tbody>
            @forelse($history as $t)
                <tr>
                    <td class="ps-3 text-nowrap">{{ $t->created_at->format('M d, Y h:i A') }}</td>
                    <td><span class="badge text-bg-{{ ['stock_in'=>'success','stock_out'=>'danger','adjustment'=>'info'][$t->type] ?? 'secondary' }}">{{ str_replace('_',' ',$t->type) }}</span></td>
                    <td class="text-end {{ $t->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $t->quantity > 0 ? '+' : '' }}{{ $t->quantity }}</td>
                    <td class="text-end fw-bold">{{ $t->balance_after }}</td>
                    <td>{{ $t->reference_number ?? '—' }}</td>
                    <td class="pe-3">{{ $t->user->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No transactions for this product{{ ($from || $to) ? ' in this date range' : '' }}.</td></tr>
            @endforelse
            </tbody>
            <tfoot class="table-light"><tr>
                <td colspan="6" class="ps-3 small text-muted">
                    Opening balance before the first recorded transaction: <strong>{{ $opening }}</strong>
                    · Current quantity: <strong>{{ $product->quantity }}</strong>
                </td>
            </tr></tfoot>
        </table>
    </div>
</div>
