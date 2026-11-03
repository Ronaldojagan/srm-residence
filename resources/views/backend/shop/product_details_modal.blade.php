<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <img src="{{ $product->image ? url('storage/product/' . $product->image) : url('assets/images/default-product.png') }}"
                 class="img-fluid rounded" alt="{{ $product->name }}">
        </div>
        <div class="col-md-6">
            <h4>{{ $product->name }}</h4>
            <p class="text-muted">{{ $product->description }}</p>

            <div class="mb-3">
                <strong>Price:</strong> {{ $currency }} {{ number_format($product->amount, 2) }}
            </div>

            <div class="mb-3">
                <strong>Offer Price:</strong> {{ $currency }} {{ number_format($product->discount_amount, 2) }}
            </div>

            <div class="mb-3">
                <strong>Category:</strong> {{ $product->category->name ?? 'N/A' }}
            </div>

            <div class="mb-3">
                <strong>Sub Category:</strong> {{ $product->subCategory->name ?? 'N/A' }}
            </div>

            <div class="mb-3">
                <strong>Shop:</strong> {{ $product->shop->name ?? 'N/A' }}
            </div>

            <div class="mb-3">
                <strong>Status:</strong>
                @if($product->approved == 1)
                    <span class="badge bg-success">Approved</span>
                @elseif($product->approved == 0)
                    <span class="badge bg-warning">Pending</span>
                @else
                    <span class="badge bg-danger">Rejected</span>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    @if($product->approved == 0)
        <button type="button" class="btn btn-success approve-product"
                data-id="{{ Crypt::encrypt($product->id) }}">
            <i class="mdi mdi-check"></i> Approve
        </button>
        <button type="button" class="btn btn-danger reject-product"
                data-id="{{ Crypt::encrypt($product->id) }}">
            <i class="mdi mdi-close"></i> Reject
        </button>
    @endif
    {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> --}}
</div>
