<div class="row">
    <div class="col-md-5">
        <img src="{{ $productRequest->product->image ? asset('storage/product/' . $productRequest->product->image) : asset('assets/images/default-product.png') }}"
            class="img-fluid rounded" alt="{{ $productRequest->product->name }}">
    </div>
    <div class="col-md-7">
        <h4>{{ $productRequest->product->name }}</h4>
        <p class="text-muted">{{ $productRequest->product->description }}</p>

        <div class="mb-3">
            <strong>Shop:</strong>
            <a href="{{ url('admin/shops/' . Crypt::encrypt($productRequest->product->shop_id)) }}" target="_blank">
                {{ $productRequest->product->shop->name }}
            </a>
        </div>

        <div class="mb-3">
            <strong>Category:</strong> {{ $productRequest->product->category->name ?? 'N/A' }}
        </div>

        <div class="mb-3">
            <strong>Subcategory:</strong> {{ $productRequest->product->subCategory->name ?? 'N/A' }}
        </div>

        <div class="mb-3">
            <strong>Price:</strong> ₹ {{ number_format($productRequest->product->amount, 2) }}
        </div>

        <div class="mb-3">
            <strong>Requested At:</strong> {{ $productRequest->product->created_at->format('d M Y, h:i A') }}
        </div>
    </div>
</div>
