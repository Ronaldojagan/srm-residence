@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <h4 class="page-title-main page">
                Coupon Details
            </h4>
        </div>
        <div class="col-md-6 mt-3 mb-3 d-flex flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/coupons') }}"
                class="btn add-btn float-end waves-effect waves-light button-color text-white">
                <i class="mdi mdi-keyboard-backspace me-1"></i> Back
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="mdi mdi-check-circle-outline me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header green-btn background-color-theme bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 text-white">
                    <i class="mdi mdi-tag-outline me-1"></i>Code: {{ $coupon->code }}
                </h5>
                <span class="badge bg-white text-primary">
                    Created: {{ $coupon->created_at->format('d M Y') }}
                </span>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Coupon Name</label>
                            <p class="fw-bold">{{ $coupon->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Discount Value</label>
                            <p class="fw-bold">
                                ₹{{ number_format($coupon->amount, 2) }}
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Minimum Purchase</label>
                            <p class="fw-bold">₹{{ number_format($coupon->min_purchase_amount, 2) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Usage Information</label>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-3">
                                    Used times
                                </span>
                                @if($coupon->usage_limit)
                                    <span class="badge {{ $coupon->used_count >= $coupon->usage_limit ? 'bg-danger' : 'bg-success' }}">
                                        {{ $coupon->used_count ?? 0 }} / {{ $coupon->usage_limit }}
                                    </span>
                                @else
                                    <span class="badge bg-info">Unlimited</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                @if($coupon->usage_limit)
                                    @if($coupon->used_count >= $coupon->usage_limit)
                                        <span class="text-danger">Coupon usage limit reached</span>
                                    @else
                                        {{ $coupon->usage_limit - $coupon->used_count }} uses remaining
                                    @endif
                                @else
                                    No usage limit
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Start Date</label>
                            <p class="fw-bold">
                                <i class="mdi mdi-calendar-start text-primary me-1"></i>
                                {{ $coupon->start_date->format('d M Y') }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Expiry Date</label>
                            <p class="fw-bold">
                                <i class="mdi mdi-calendar-end text-danger me-1"></i>
                                {{ $coupon->expire_date->format('d M Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Validity Status</label>
                            <div>
                                @if($coupon->isExpired())
                                    <span class="badge bg-danger">Expired</span>
                                @elseif(!$coupon->canBeUsed())
                                    <span class="badge bg-warning">Cannot be used</span>
                                @else
                                    <span class="badge bg-success">Valid</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small mb-1">Status</label>
                            <div>
                               <span class="badge {{ $coupon->status ? 'bg-success' : 'bg-danger' }}">
                                    {{ $coupon->status ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if ($coupon->description)
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Description</label>
                            <div class="card bg-light p-3 border">
                                {!! nl2br(e($coupon->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header green-btn text-white">
                            <h6 class="card-title text-white mb-0">Coupon Image</h6>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            @if ($coupon->image)
                                <div class="text-center">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal">
                                        <img src="{{ url('storage/coupon/' . $coupon->image) }}" alt="Coupon Image"
                                            class="img-fluid rounded border cursor-pointer"
                                            style="max-height: 200px; cursor: pointer;">
                                    </a>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-theme-second-success" data-bs-toggle="modal"
                                            data-bs-target="#imageModal">
                                            <i class="mdi mdi-eye-outline me-1"></i> View Full Image
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="mdi mdi-image-off-outline display-6"></i>
                                    <p class="mt-2">No image available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Coupon Image: {{ $coupon->code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ url('storage/coupon/' . $coupon->image) }}" class="img-fluid" alt="Full Coupon Image">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('.cursor-pointer').hover(function() {
                    $(this).css('transform', 'scale(1.02)');
                    $(this).css('transition', 'transform 0.3s ease');
                }, function() {
                    $(this).css('transform', 'scale(1)');
                });

                @if (Session::has('success'))
                    Swal.fire({
                        title: "Success!",
                        text: "{{ Session::get('success') }}",
                        icon: "success",
                        confirmButtonColor: "#3b5de7",
                        timer: 3000,
                        timerProgressBar: true
                    });
                @endif
            });
        </script>
    @endpush
@endsection
