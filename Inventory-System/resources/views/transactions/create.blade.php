@extends('layouts.app')

@section('title', 'Record Transaction')

@section('content')
    <div class="pb-2 mb-3 border-bottom">
        <h2>Record Inventory Transaction</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="product_id" class="form-label">Product</label>
                        <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                            <option value="">-- Select Product --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-quantity="{{ $product->quantity }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted">Current stock: <span id="currentStockDisplay">—</span></small>
                    </div>

                    <div class="col-md-6">
                        <label for="type" class="form-label">Transaction Type</label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">-- Select Type --</option>
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" step="1" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}" required>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted">Enter a positive amount for Stock In / Stock Out. For Adjustment, use the sign to show increase (+) or decrease (-).</small>
                    </div>

                    <div class="col-md-6">
                        <label for="reference_number" class="form-label">Reference Number (optional)</label>
                        <input type="text" class="form-control @error('reference_number') is-invalid @enderror" id="reference_number" name="reference_number" value="{{ old('reference_number') }}" placeholder="e.g. PO-1042, SO-2089">
                        @error('reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const productSelect = document.getElementById('product_id');
        const typeSelect = document.getElementById('type');
        const quantityInput = document.getElementById('quantity');
        const stockDisplay = document.getElementById('currentStockDisplay');

        function updateStockDisplay() {
            const selected = productSelect.options[productSelect.selectedIndex];
            const qty = selected ? selected.dataset.quantity : null;
            stockDisplay.textContent = qty !== undefined && qty !== null ? qty : '—';

            if (typeSelect.value === 'stock_out' && qty !== undefined) {
                quantityInput.setAttribute('max', qty);
            } else {
                quantityInput.removeAttribute('max');
            }
        }

        productSelect.addEventListener('change', updateStockDisplay);
        typeSelect.addEventListener('change', updateStockDisplay);
    </script>
@endsection