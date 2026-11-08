@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <h3 class="fw-bold mb-3">Stock Adjustment</h3>

    <div class="card border-0 shadow-sm" style="max-width: 720px;">
        <div class="card-header bg-white fw-bold py-3">
            Reconcile system quantity with a physical count
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('adjustments.store') }}">
                @csrf

                {{-- Product selection --}}
                <div class="mb-3">
                    <label for="product_id" class="form-label fw-semibold">Product</label>
                    <select name="product_id" id="product_id"
                            class="form-select @error('product_id') is-invalid @enderror" required>
                        <option value="">— Select a product —</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Current system quantity: read-only, loaded via AJAX --}}
                <div class="mb-3">
                    <label for="system_quantity" class="form-label fw-semibold">Current system quantity</label>
                    <input type="text" id="system_quantity" class="form-control" value="—" readonly>
                </div>

                {{-- Physical count --}}
                <div class="mb-3">
                    <label for="physical_count" class="form-label fw-semibold">Physical count</label>
                    <input type="number" name="physical_count" id="physical_count" min="0" step="1"
                           value="{{ old('physical_count') }}"
                           class="form-control @error('physical_count') is-invalid @enderror" required>
                    @error('physical_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text" id="difference-preview"></div>
                </div>

                {{-- Reason --}}
                <div class="mb-3">
                    <label for="reason" class="form-label fw-semibold">Reason for adjustment</label>
                    <select name="reason" id="reason"
                            class="form-select @error('reason') is-invalid @enderror" required>
                        <option value="">— Select a reason —</option>
                        @foreach($reasons as $key => $label)
                            <option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <label for="notes" class="form-label fw-semibold">Notes (optional)</label>
                    <textarea name="notes" id="notes" rows="3" maxlength="1000"
                              class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-danger">Submit Adjustment</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const select   = document.getElementById('product_id');
    const sysQty   = document.getElementById('system_quantity');
    const physical = document.getElementById('physical_count');
    const preview  = document.getElementById('difference-preview');
    let systemQuantity = null;

    function updatePreview() {
        if (systemQuantity === null || physical.value === '') { preview.textContent = ''; return; }
        const diff = parseInt(physical.value, 10) - systemQuantity;
        if (diff === 0)      { preview.textContent = 'No difference — no adjustment will be created.'; preview.className = 'form-text text-muted'; }
        else if (diff > 0)   { preview.textContent = `Difference: +${diff} (stock will increase)`;     preview.className = 'form-text text-success'; }
        else                 { preview.textContent = `Difference: ${diff} (stock will decrease)`;      preview.className = 'form-text text-danger'; }
    }

    async function loadQuantity() {
        systemQuantity = null;
        sysQty.value = '—';
        updatePreview();
        if (!select.value) return;

        sysQty.value = 'Loading…';
        try {
            const res = await fetch(`{{ url('/api/products') }}/${select.value}/quantity`, {
                headers: { 'Accept': 'application/json' }, credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();
            systemQuantity = json.data.quantity;
            sysQty.value = systemQuantity;
            updatePreview();
        } catch (e) {
            sysQty.value = 'Could not load quantity';
            console.error(e);
        }
    }

    select.addEventListener('change', loadQuantity);
    physical.addEventListener('input', updatePreview);
    if (select.value) loadQuantity();   // keep working after a validation error redirect
})();
</script>
@endsection