@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Inventory Reports</h3>
        <button class="btn btn-outline-secondary btn-sm d-print-none" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print / Save as PDF
        </button>
    </div>

    <ul class="nav nav-tabs mb-3 d-print-none" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-movement" type="button">Stock Movement</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-low" type="button">Low Stock ({{ $lowStock->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-valuation" type="button">Inventory Valuation</button></li>
    </ul>

    <div class="tab-content">

        {{-- ============ Stock Movement Report ============ --}}
        <div class="tab-pane fade show active" id="tab-movement">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-end d-print-none">
                        <div class="col-auto">
                            <label class="form-label small mb-0">From</label>
                            <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-0">To</label>
                            <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto"><button class="btn btn-danger btn-sm">Generate</button></div>
                        <div class="col-auto">
                            <a class="btn btn-outline-success btn-sm" href="{{ route('reports.export', ['type' => 'movement', 'from' => $from, 'to' => $to]) }}">
                                <i class="bi bi-filetype-csv me-1"></i> Export CSV
                            </a>
                        </div>
                    </form>
                    <div class="fw-bold mt-2">Stock Movement: {{ $from }} to {{ $to }}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light"><tr>
                            <th class="ps-3">Date/Time</th><th>Product</th><th>Type</th>
                            <th class="text-end">Qty</th><th>Reference</th><th class="pe-3">User</th>
                        </tr></thead>
                        <tbody>
                        @forelse($movements as $t)
                            <tr>
                                <td class="ps-3 text-nowrap">{{ $t->created_at->format('M d, Y h:i A') }}</td>
                                <td>{{ $t->product->name ?? '—' }}</td>
                                <td><span class="badge text-bg-{{ ['stock_in'=>'success','stock_out'=>'danger','adjustment'=>'info'][$t->type] ?? 'secondary' }}">{{ str_replace('_',' ',$t->type) }}</span></td>
                                <td class="text-end {{ $t->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $t->quantity > 0 ? '+' : '' }}{{ $t->quantity }}</td>
                                <td>{{ $t->reference_number ?? '—' }}</td>
                                <td class="pe-3">{{ $t->user->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No transactions in this date range.</td></tr>
                        @endforelse
                        </tbody>
                        @if($movements->count())
                        <tfoot class="table-light"><tr>
                            <td colspan="3" class="ps-3 fw-bold">Net movement ({{ $movements->count() }} transactions)</td>
                            <td class="text-end fw-bold">{{ $movements->sum('quantity') > 0 ? '+' : '' }}{{ $movements->sum('quantity') }}</td>
                            <td colspan="2"></td>
                        </tr></tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- ============ Low Stock Report ============ --}}
        <div class="tab-pane fade" id="tab-low">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Products at or below reorder level</span>
                    <a class="btn btn-outline-success btn-sm d-print-none" href="{{ route('reports.export', 'low-stock') }}"><i class="bi bi-filetype-csv me-1"></i> Export CSV</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light"><tr>
                            <th class="ps-3">SKU</th><th>Product</th><th>Category</th>
                            <th class="text-end">Qty</th><th class="text-end">Reorder Level</th><th class="pe-3">Status</th>
                        </tr></thead>
                        <tbody>
                        @forelse($lowStock as $p)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $p->sku }}</td>
                                <td><a href="{{ route('products.show', $p) }}">{{ $p->name }}</a></td>
                                <td>{{ $p->category }}</td>
                                <td class="text-end">{{ $p->quantity }}</td>
                                <td class="text-end">{{ $p->reorder_level }}</td>
                                <td class="pe-3"><span class="badge text-bg-{{ $p->stock_status_color }}">{{ $p->stock_status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-success py-4">All products are sufficiently stocked.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ============ Inventory Valuation Report ============ --}}
        <div class="tab-pane fade" id="tab-valuation">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total inventory value: <span class="text-success">${{ number_format($totalValue, 2) }}</span></span>
                    <a class="btn btn-outline-success btn-sm d-print-none" href="{{ route('reports.export', 'valuation') }}"><i class="bi bi-filetype-csv me-1"></i> Export CSV</a>
                </div>
                <div class="card-body border-bottom">
                    <div class="row g-2">
                        @foreach($byCategory as $category => $data)
                            <div class="col-md-2 col-6">
                                <div class="border rounded p-2 text-center">
                                    <div class="small text-muted">{{ $category }}</div>
                                    <div class="fw-bold">${{ number_format($data['value'], 2) }}</div>
                                    <div class="small text-muted">{{ $data['units'] }} units</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light"><tr>
                            <th class="ps-3">SKU</th><th>Product</th><th>Category</th>
                            <th class="text-end">Qty</th><th class="text-end">Unit Price</th><th class="text-end pe-3">Total Value</th>
                        </tr></thead>
                        <tbody>
                        @foreach($products as $p)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $p->sku }}</td>
                                <td>{{ $p->name }}</td>
                                <td>{{ $p->category }}</td>
                                <td class="text-end">{{ $p->quantity }}</td>
                                <td class="text-end">${{ number_format($p->unit_price, 2) }}</td>
                                <td class="text-end pe-3">${{ number_format($p->quantity * $p->unit_price, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="table-light"><tr>
                            <td colspan="5" class="ps-3 fw-bold text-end">TOTAL</td>
                            <td class="text-end pe-3 fw-bold">${{ number_format($totalValue, 2) }}</td>
                        </tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
