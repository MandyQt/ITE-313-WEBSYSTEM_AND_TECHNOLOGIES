@extends('layouts.app')

@section('content')
<div class="container-fluid p-0"
     id="realtime-dashboard"
     data-stats-url="{{ url('/api/dashboard/stats') }}"
     data-low-stock-url="{{ url('/api/products/low-stock') }}"
     data-recent-url="{{ url('/api/transactions/recent') }}"
     data-interval="30000">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Good Day, {{ Auth::user()->name ?? 'User' }}!</h3>
        <small class="text-muted">
            Last updated: <span id="last-updated">just now</span>
            <span id="poll-error" class="text-danger ms-2 d-none">Connection problem – retrying…</span>
        </small>
    </div>

    @if(($lowStockCount ?? 0) > 0 || ($outOfStockCount ?? 0) > 0)
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
            <div>
                <strong>Warning:</strong> {{ ($lowStockCount ?? 0) + ($outOfStockCount ?? 0) }} item(s) are low on stock or out of stock.
                <a href="{{ route('products.index') }}" class="alert-link">View Products</a>
            </div>
        </div>
    @endif

    <!-- Statistics Cards (IDs are targeted by public/js/dashboard.js) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="card-body p-2">
                    <small class="text-muted fw-bold">TOTAL PRODUCTS</small>
                    <h3 class="fw-bold text-dark mt-2 mb-0" id="stat-total-products">{{ $totalProducts ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="card-body p-2">
                    <small class="text-muted fw-bold">INVENTORY VALUE</small>
                    <h3 class="fw-bold text-success mt-2 mb-0">$<span id="stat-total-value">{{ number_format($totalValue ?? 0, 2) }}</span></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="card-body p-2">
                    <small class="text-muted fw-bold">LOW STOCK ITEMS</small>
                    <h3 class="fw-bold text-warning mt-2 mb-0" id="stat-low-stock">{{ $lowStockCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="card-body p-2">
                    <small class="text-muted fw-bold">OUT OF STOCK</small>
                    <h3 class="fw-bold text-danger mt-2 mb-0" id="stat-out-of-stock">{{ $outOfStockCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts + Recent Transactions (filled by JavaScript) -->
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold py-3">Low Stock Alerts</div>
                <ul class="list-group list-group-flush" id="low-stock-list">
                    <li class="list-group-item text-muted">Loading…</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold py-3">Recent Transactions</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">When</th><th>Product</th><th>Type</th>
                                <th class="text-end">Qty</th><th class="pe-3">User</th>
                            </tr>
                        </thead>
                        <tbody id="recent-transactions-body">
                            <tr><td colspan="5" class="text-muted ps-3">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold py-3">Inventory Value by Category</div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold py-3">Stock Status</div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="stockStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recently Added Products Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold py-3">
            Recently Added Products
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th class="pe-3">Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentProducts as $product)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $product->sku }}</td>
                                <td>{{ $product->name }}</td>
                                <td><span class="badge bg-secondary">{{ $product->category }}</span></td>
                                <td>{{ $product->quantity }}</td>
                                <td class="pe-3 text-muted">{{ $product->created_at ? $product->created_at->diffForHumans() : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No products added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Category Value Bar Chart
    const categoryLabels = @json($categorySummary->pluck('category') ?? []);
    const categoryValues = @json($categorySummary->pluck('total_value') ?? []);

    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Inventory Value ($)',
                data: categoryValues,
                backgroundColor: ['#8b0000', '#c0392b', '#e74c3c', '#d35400', '#e67e22', '#f39c12']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // Stock Status Doughnut Chart
    const inStock = {{ max(0, ($totalProducts ?? 0) - (($lowStockCount ?? 0) + ($outOfStockCount ?? 0))) }};
    const lowStock = {{ $lowStockCount ?? 0 }};
    const outStock = {{ $outOfStockCount ?? 0 }};

    const stockStatusChart = new Chart(document.getElementById('stockStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['In Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [inStock, lowStock, outStock],
                backgroundColor: ['#28a745', '#ffc107', '#c0392b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Keep the doughnut in sync with each AJAX poll (event fired by dashboard.js)
    document.addEventListener('dashboard:stats', (e) => {
        const s = e.detail;
        stockStatusChart.data.datasets[0].data = [
            Math.max(0, s.total_products - s.low_stock_count - s.out_of_stock_count),
            s.low_stock_count,
            s.out_of_stock_count
        ];
        stockStatusChart.update();
    });
</script>

{{-- Part 7: AJAX polling every 30 seconds --}}
<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection
