@extends('backend.layout.main')
@section('content')
@push('head_content')
<link href="{{ url('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
@endpush
    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <h4 class="page-title-main page">
                {{ isset($edit) ? 'Edit Coupon' : 'Create Coupon' }}
            </h4>
        </div>
        <div class="col-md-6 mt-3 mb-3 d-flex flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/coupons') }}" class="btn add-btn float-end waves-effect waves-light button-color text-white">
                <i class="mdi mdi-keyboard-backspace me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        @if (session('status'))
            <div class="alert alert-success bg-success h5 text-light p-2">
                {{ session('status') }}
            </div>
        @endif

        <div class="card save-card card-shadow col-12 input-background">
            <div class="card-body">
                <form action="{{ isset($edit) ? url('/admin/coupons/' . Crypt::encrypt($edit->id)) : url('/admin/coupons') }}"
                    method="post" class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                    @if(isset($edit)) @method('PUT') @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Coupon Name</label>
                            <input type="text" name="name" class="form-control input-form @error('name') is-invalid @enderror"
                                placeholder="Enter Coupon Name" value="{{ old('name', $edit->name ?? '') }}">
                            @error('name')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Coupon Code</label>
                            <input type="text" name="code" class="form-control input-form @error('code') is-invalid @enderror"
                                placeholder="Enter Coupon Code" value="{{ old('code', $edit->code ?? '') }}">
                            @error('code')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Discount Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control input-form @error('amount') is-invalid @enderror"
                                placeholder="Enter Amount" value="{{ old('amount', $edit->amount ?? '') }}">
                            @error('amount')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Minimum Purchase Amount</label>
                            <input type="number" name="min_purchase_amount" class="form-control input-form @error('min_purchase_amount') is-invalid @enderror"
                                placeholder="Enter Min Order Amount" value="{{ old('min_purchase_amount', $edit->min_purchase_amount ?? '') }}">
                            @error('min_purchase_amount')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Usage Limit</label>
                            <input type="number" name="usage_limit" class="form-control input-form @error('usage_limit') is-invalid @enderror"
                                placeholder="Leave empty for unlimited usage"
                                value="{{ old('usage_limit', $edit->usage_limit ?? '') }}"
                                min="1">
                            <small class="text-muted">Enter maximum number of times this coupon can be used. Leave empty for unlimited.</small>
                            @error('usage_limit')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        @if(isset($edit))
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Times Used</label>
                            <div class="form-control bg-light" style="border-radius: var(--border-radius);">
                                <span class="fw-bold">{{ $edit->used_count ?? 0 }}</span>
                                @if($edit->usage_limit)
                                    <span class="text-muted float-end">
                                        {{ $edit->used_count }} / {{ $edit->usage_limit }}
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted">Number of times this coupon has been used.</small>
                        </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Start Date</label>
                            <input type="date" name="start_date" class="form-control input-form @error('start_date') is-invalid @enderror" id="start-datepicker"
                                value="{{ old('start_date', isset($edit->start_date) ? \Carbon\Carbon::parse($edit->start_date)->format('Y-m-d') : '') }}" placeholder="Select Start Date">
                            @error('start_date')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Expire Date</label>
                            <input type="date" name="expire_date" class="form-control input-form @error('expire_date') is-invalid @enderror" id="expire-datepicker"
                                value="{{ old('expire_date', isset($edit->expire_date) ? \Carbon\Carbon::parse($edit->expire_date)->format('Y-m-d') : '') }}" placeholder="Select End Date">
                            @error('expire_date')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Image</label>
                            <div class="file-input-group">
                                <div class="input-group">
                                    <input type="file"
                                        class="form-control input-form @error('image') is-invalid @enderror" name="image"
                                        id="imageUpload" accept="image/*">
                                </div>

                                <div class="file-info mt-2" id="fileInfo">
                                    @if (isset($edit) && $edit->image)
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="mdi mdi-file-image me-1"></i> {{ $edit->image }}
                                            </span>
                                            <a href="javascript:void(0);" class="open-image-model text-primary"
                                                data-url="{{ url('storage/coupon/' . $edit->image) }}">
                                                <i class="mdi mdi-eye me-1"></i> View
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">No file selected</span>
                                    @endif
                                </div>

                                <div class="image-preview mt-2" id="imagePreview"
                                    @if (isset($edit) && $edit->image) style="display: block;"
                                        @else
                                            style="display: none;" @endif>
                                    @if (isset($edit) && $edit->image)
                                        <img src="{{ url('storage/coupon/' . $edit->image) }}" alt="Current Image"
                                            class="img-thumbnail preview-image" style="max-height: 150px;">
                                    @endif
                                </div>
                            </div>

                            @error('image')
                                <div class="invalid-feedback d-block">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Description</label>
                            <textarea name="description" class="form-control input-form @error('description') is-invalid @enderror"
                                placeholder="Enter description" rows="5">{{ old('description', $edit->description ?? '') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Status</label>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input
                                            type="radio"
                                            id="active"
                                            name="status"
                                            class="form-check-input"
                                            value="1"
                                            {{ old('status', isset($edit) ? $edit->status : '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">Active</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input
                                            type="radio"
                                            id="inactive"
                                            name="status"
                                            class="form-check-input"
                                            value="0"
                                            {{ old('status', isset($edit) ? $edit->status : '') == '0' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="inactive">Inactive</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 submit text-end">
                        <button type="submit"
                            class="btn waves-effect waves-light button-color save-width input-form text-white account-save">
                            {{ isset($edit) ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('footer_content')
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"
        integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="{{ url('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#expire-datepicker").flatpickr({
                dateFormat: "d-m-Y"
            });

            $("#start-datepicker").flatpickr({
                dateFormat: "d-m-Y"
            });

            $('#imageUpload').on('change', function() {
                const file = this.files[0];
                const fileInfo = $('#fileInfo');
                const preview = $('#imagePreview');

                if (file) {
                    fileInfo.html(`
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-dark me-2">
                                <i class="mdi mdi-file-image me-1"></i> ${file.name}
                            </span>
                            <span class="text-muted">(${(file.size/1024).toFixed(2)} KB)</span>
                        </div>
                    `);

                    if (file.type.match('image.*')) {
                        const reader = new FileReader();

                        reader.onload = function(e) {
                            preview.html(`
                                <img src="${e.target.result}"
                                     alt="Preview"
                                     class="img-thumbnail preview-image"
                                     style="max-height: 150px;">
                            `).show();
                        }

                        reader.readAsDataURL(file);
                    }
                } else {
                    @if (isset($edit) && $edit->image)
                        fileInfo.html(`
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-dark me-2">
                                    <i class="mdi mdi-file-image me-1"></i> {{ $edit->image }}
                                </span>
                                <a href="javascript:void(0);"
                                   class="open-image-model text-primary"
                                   data-url="{{ url('storage/coupon/' . $edit->image) }}">
                                    <i class="mdi mdi-eye me-1"></i> View
                                </a>
                            </div>
                        `);
                        preview.show();
                    @else
                        fileInfo.html('<span class="text-muted">No file selected</span>');
                        preview.hide();
                    @endif
                }
            });
        });
    </script>
    @endpush
@endsection
