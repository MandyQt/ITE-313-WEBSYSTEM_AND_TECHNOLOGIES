<div class="row g-3">
    <!-- Product Name -->
    <div class="col-md-6">
        <label for="name" class="form-label fw-semibold">Product Name</label>
        <input type="text" 
               class="form-control @error('name') is-invalid @enderror" 
               id="name" 
               name="name" 
               value="{{ old('name', $product->name ?? '') }}" 
               required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- SKU -->
    <div class="col-md-6">
        <label for="sku" class="form-label fw-semibold">SKU (Stock Keeping Unit)</label>
        <input type="text" 
               class="form-control @error('sku') is-invalid @enderror" 
               id="sku" 
               name="sku" 
               value="{{ old('sku', $product->sku ?? '') }}" 
               required>
        @error('sku')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Category -->
    <div class="col-md-6">
        <label for="category" class="form-label fw-semibold">Category</label>
        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
            <option value="" disabled {{ empty($product->category) ? 'selected' : '' }}>Select category...</option>
            @php
                $categories = ['Lumber', 'Hardware', 'Tools', 'Electrical', 'Plumbing', 'Paint'];
            @endphp
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ old('category', $product->category ?? '') == $cat ? 'selected' : '' }}>
                    {{ $cat }}
                </option>
            @endforeach
        </select>
        @error('category')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Supplier -->
    <div class="col-md-6">
        <label for="supplier" class="form-label fw-semibold">Supplier</label>
        <input type="text" 
               class="form-control @error('supplier') is-invalid @enderror" 
               id="supplier" 
               name="supplier" 
               value="{{ old('supplier', $product->supplier ?? '') }}">
        @error('supplier')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Quantity -->
    <div class="col-md-4">
        <label for="quantity" class="form-label fw-semibold">Current Quantity</label>
        <input type="number" 
               class="form-control @error('quantity') is-invalid @enderror" 
               id="quantity" 
               name="quantity" 
               min="0" 
               value="{{ old('quantity', $product->quantity ?? 0) }}" 
               required>
        @error('quantity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Reorder Level -->
    <div class="col-md-4">
        <label for="reorder_level" class="form-label fw-semibold">Reorder Level Threshold</label>
        <input type="number" 
               class="form-control @error('reorder_level') is-invalid @enderror" 
               id="reorder_level" 
               name="reorder_level" 
               min="0" 
               value="{{ old('reorder_level', $product->reorder_level ?? 10) }}" 
               required>
        @error('reorder_level')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Unit Price -->
    <div class="col-md-4">
        <label for="unit_price" class="form-label fw-semibold">Unit Price ($)</label>
        <input type="number" 
               step="0.01" 
               class="form-control @error('unit_price') is-invalid @enderror" 
               id="unit_price" 
               name="unit_price" 
               min="0" 
               value="{{ old('unit_price', $product->unit_price ?? '') }}" 
               required>
        @error('unit_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Description -->
    <div class="col-12">
        <label for="description" class="form-label fw-semibold">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror" 
                  id="description" 
                  name="description" 
                  rows="3">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>