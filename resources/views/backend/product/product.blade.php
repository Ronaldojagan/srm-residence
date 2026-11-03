@extends('backend.layout.main')
@section('content')
    @push('head_content')
    @endpush
    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <h4 class="page-title-main page">
                {{ isset($edit) ? 'Edit Product' : 'Create Product' }}
            </h4>
        </div>
        <div class="col-md-6 mt-3 mb-3 d-flex flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/products') }}"
                class="btn add-btn float-end waves-effect waves-light button-color text-white">
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
                <form
                    action="{{ isset($edit) ? url('/admin/products/' . Crypt::encrypt($edit->id)) : url('/admin/products') }}"
                    method="post" class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                    @if (isset($edit))
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Product Name</label>
                            <input type="text" name="name"
                                class="form-control input-form @error('name') is-invalid @enderror"
                                placeholder="Enter Product Name" value="{{ old('name', $edit->name ?? '') }}">
                            @error('name')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Category</label>
                            <select name="category_id" id="category-select"
                                class="form-control input-form @error('category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach ($category as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id', $edit->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label text-dark">Sub Category</label>
                            <select name="sub_category_id" id="subcategory-select"
                                class="form-control input-form @error('sub_category_id') is-invalid @enderror">
                                <option value="">Select Sub Category</option>
                            </select>
                            @error('sub_category_id')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label text-dark">Brand</label>
                            <select name="brand_id" class="form-control input-form @error('brand_id') is-invalid @enderror">
                                <option value="">Select Brand</option>
                                @foreach ($brand as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('brand_id', $edit->brand_id ?? '') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Quantity</label>
                            <input type="text" pattern="^\d+(/\d+)?$" name="quantity"
                                class="form-control input-form @error('quantity') is-invalid @enderror"
                                placeholder="Enter Quantity" value="{{ old('quantity', $edit->quantity ?? '') }}">
                            @error('quantity')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Product Total Stock</label>
                            <input type="number" name="product_stock"
                                class="form-control input-form @error('product_stock') is-invalid @enderror"
                                value="{{ old('product_stock', $edit->product_stock ?? '') }}"
                                placeholder="Enter Product Stock" @if (isset($edit)) readonly @endif>
                            @error('product_stock')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Amount</label>
                            <input type="number" step="0.01" name="amount" id="amount"
                                class="form-control input-form @error('amount') is-invalid @enderror"
                                value="{{ old('amount', $edit->amount ?? '') }}" placeholder="Enter Amount">
                            @error('amount')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Selling Amount</label>
                            <input type="number" step="0.01" name="discount_amount" id="discount_amount"
                                class="form-control input-form @error('discount_amount') is-invalid @enderror"
                                value="{{ old('discount_amount', $edit->discount_amount ?? '') }}"
                                placeholder="Enter Selling Amount">
                            @error('discount_amount')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Discount Percentage</label>
                            <input type="number" step="0.01" name="discount_percentage" id="discount_percentage"
                                class="form-control input-form @error('discount_percentage') is-invalid @enderror"
                                value="{{ old('discount_percentage', $edit->discount_percentage ?? '') }}"
                                placeholder="Enter Discount Percentage" readonly>
                            @error('discount_percentage')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Min Product Stock Alert</label>
                            <input type="number" name="max_product_stock_alert"
                                class="form-control input-form @error('max_product_stock_alert') is-invalid @enderror"
                                value="{{ old('max_product_stock_alert', $edit->max_product_stock_alert ?? '') }}"
                                placeholder="Enter Min Product Stock Alert">
                            @error('max_product_stock_alert')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Status</label>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="radio" id="active" name="status" class="form-check-input"
                                            value="1"
                                            {{ old('status', isset($edit) ? (int) $edit->status : 1) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">Active</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="radio" id="inactive" name="status" class="form-check-input"
                                            value="0"
                                            {{ old('status', isset($edit) ? (int) $edit->status : '') == 0 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="inactive">Inactive</label>
                                    </div>
                                </div>
                            </div>

                            @error('status')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Main Image</label>
                            <input type="file" name="image" id="main-images-input"
                                class="form-control input-form @error('image') is-invalid @enderror">
                            <div class="row mt-2" id="main-images-preview">
                            </div>
                            @if (isset($edit) && $edit->image)
                                <div class="mt-2">
                                    <img src="{{ url('storage/product/' . $edit->image) }}" class="img-thumbnail"
                                        style="max-height: 100px;">
                                    <a href="javascript:void(0);" class="open-image-model d-block"
                                        data-url="{{ url('storage/product/' . $edit->image) }}">
                                        View Full Image
                                    </a>
                                </div>
                            @endif
                            @error('image')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark">Additional Images (Max 2)</label>

                            <input type="file" name="additional_images[]" multiple
                                class="form-control input-form @if ($errors->has('additional_images') || $errors->has('additional_images.*')) is-invalid @endif"
                                accept="image/*" id="additional-images-input">

                            @if ($errors->has('additional_images'))
                                <span class="invalid-feedback d-block">
                                    <strong>{{ $errors->first('additional_images') }}</strong>
                                </span>
                            @endif

                            @foreach ($errors->get('additional_images.*') as $messages)
                                @foreach ($messages as $message)
                                    <span class="invalid-feedback d-block">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @endforeach
                            @endforeach

                            @if (isset($edit))
                                <input type="hidden" name="deleted_additional_images" id="deleted-additional-images"
                                    value="">
                            @endif

                            <div class="row mt-2" id="additional-images-preview">
                                @if (isset($edit) && $edit->additionalImages->count() > 0)
                                    @foreach ($edit->additionalImages as $image)
                                        <div class="col-md-4 mb-2 position-relative existing-image"
                                            data-image-id="{{ $image->id }}">
                                            <img src="{{ $image->image_path ? url('storage/product/' . $image->image_path) : asset('images/default-product.png') }}"
                                                class="img-thumbnail"
                                                style="max-height: 80px; width: 100%; object-fit: cover;">
                                            <button type="button"
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-image-btn"
                                                onclick="removeAdditionalImage(this, {{ $image->id }})">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @endif

                            </div>

                            <small class="text-muted">You can upload up to 2 additional images. Total images cannot exceed
                                2.</small>
                        </div>

                        <!-- YouTube Videos Section -->
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">YouTube Videos</h5>
                                    <small class="text-muted">Add YouTube video links for product demonstrations, reviews,
                                        or tutorials (Max 5 videos)</small>
                                </div>
                                <div class="card-body">
                                    <div id="youtube-videos-container">
                                        @php
                                            $existingVideos = [];
                                            if (isset($edit) && $edit->youtube_videos) {
                                                $existingVideos = json_decode($edit->youtube_videos, true) ?? [];
                                            }
                                        @endphp

                                        @if (!empty($existingVideos))
                                            @foreach ($existingVideos as $index => $video)
                                                <div class="youtube-video-item border rounded p-3 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-10 mb-3">
                                                            <label class="form-label text-dark">YouTube Video URL <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="url"
                                                                name="youtube_videos[{{ $index }}][url]"
                                                                class="form-control input-form youtube-url-input"
                                                                placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID"
                                                                value="{{ old("youtube_videos.{$index}.url", $video['url'] ?? '') }}"
                                                                required>
                                                            <div class="invalid-feedback d-none youtube-url-error">
                                                                Please enter a valid YouTube URL
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 mb-3 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-danger remove-youtube-btn"
                                                                onclick="removeYoutubeVideo(this)">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    @if (!empty($video['url']) && $video['url'])
                                                        <div class="video-preview mb-3">
                                                            <label class="form-label text-dark">Preview:</label>
                                                            <div class="embed-responsive embed-responsive-16by9">
                                                                <iframe class="embed-responsive-item"
                                                                    src="{{ $video['url']  }}"
                                                                    frameborder="0"
                                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                                    allowfullscreen style="width: 100%; height: 200px;">
                                                                </iframe>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <div class="text-center mt-3">
                                        <button type="button" class="btn btn-primary" onclick="addYoutubeVideo()">
                                            <i class="fa fa-plus"></i> Add YouTube Video
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Additional Information Section -->
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Additional Product Information</h5>
                                    <small class="text-muted">Add detailed information about your product (e.g., Features,
                                        Materials, Specifications, etc.)</small>
                                </div>
                                <div class="card-body">
                                    <div id="additional-info-container">
                                        @php
                                            // Parse existing additional information
                                            $existingInfo = [];
                                            if (isset($edit) && $edit->additional_info) {
                                                $existingInfo = json_decode($edit->additional_info, true) ?? [];
                                            }
                                        @endphp

                                        @if (!empty($existingInfo))
                                            @foreach ($existingInfo as $index => $info)
                                                <div class="additional-info-item border rounded p-3 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-10 mb-3">
                                                            <label class="form-label text-dark">Information Heading</label>
                                                            <input type="text"
                                                                name="additional_info[{{ $index }}][heading]"
                                                                class="form-control input-form"
                                                                placeholder="e.g., Features, Materials, Specifications"
                                                                value="{{ old("additional_info.{$index}.heading", $info['heading'] ?? '') }}">
                                                        </div>
                                                        <div class="col-md-2 mb-3 d-flex align-items-end">
                                                            <button type="button" class="btn btn-danger remove-info-btn"
                                                                onclick="removeAdditionalInfo(this)">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="info-details-container">
                                                        @if (!empty($info['items']))
                                                            @foreach ($info['items'] as $itemIndex => $item)
                                                                <div class="row info-detail-item mb-2">
                                                                    <div class="col-md-5">
                                                                        <label class="form-label text-dark">Title</label>
                                                                        <input type="text"
                                                                            name="additional_info[{{ $index }}][items][{{ $itemIndex }}][title]"
                                                                            class="form-control input-form"
                                                                            placeholder="e.g., Brand Name, Color, Size"
                                                                            value="{{ old("additional_info.{$index}.items.{$itemIndex}.title", $item['title'] ?? '') }}">
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <label class="form-label text-dark">Value</label>
                                                                        <input type="text"
                                                                            name="additional_info[{{ $index }}][items][{{ $itemIndex }}][value]"
                                                                            class="form-control input-form"
                                                                            placeholder="e.g., Vega, Red, Large"
                                                                            value="{{ old("additional_info.{$index}.items.{$itemIndex}.value", $item['value'] ?? '') }}">
                                                                    </div>
                                                                    <div class="col-md-2 d-flex align-items-end">
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-outline-danger"
                                                                            onclick="removeInfoDetail(this)">
                                                                            <i class="fa fa-times"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>

                                                    <div class="text-end mt-2">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary add-detail-btn"
                                                            onclick="addInfoDetail(this)">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <!-- Default empty section -->
                                            <div class="additional-info-item border rounded p-3 mb-3">
                                                <div class="row">
                                                    <div class="col-md-10 mb-3">
                                                        <label class="form-label text-dark">Information Heading</label>
                                                        <input type="text" name="additional_info[0][heading]"
                                                            class="form-control input-form"
                                                            placeholder="e.g., Features, Materials, Specifications"
                                                            value="{{ old('additional_info.0.heading', '') }}">
                                                    </div>
                                                    <div class="col-md-2 mb-3 d-flex align-items-end">
                                                        <button type="button" class="btn btn-danger remove-info-btn"
                                                            onclick="removeAdditionalInfo(this)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="info-details-container">
                                                    <div class="row info-detail-item mb-2">
                                                        <div class="col-md-5">
                                                            <label class="form-label text-dark">Title</label>
                                                            <input type="text"
                                                                name="additional_info[0][items][0][title]"
                                                                class="form-control input-form"
                                                                placeholder="e.g., Brand Name, Color, Size"
                                                                value="{{ old('additional_info.0.items.0.title', '') }}">
                                                        </div>
                                                        <div class="col-md-5">
                                                            <label class="form-label text-dark">Value</label>
                                                            <input type="text"
                                                                name="additional_info[0][items][0][value]"
                                                                class="form-control input-form"
                                                                placeholder="e.g., Vega, Red, Large"
                                                                value="{{ old('additional_info.0.items.0.value', '') }}">
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="removeInfoDetail(this)">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-end mt-2">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary add-detail-btn"
                                                        onclick="addInfoDetail(this)">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-center mt-3">
                                        <button type="button" class="btn btn-primary" onclick="addAdditionalInfo()">
                                            <i class="fa fa-plus"></i> Add New Information Section
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label text-dark">Description</label>
                            <textarea name="description" class="form-control input-form @error('description') is-invalid @enderror"
                                placeholder="Enter Description" rows="5" placeholder="Product description...">{{ old('description', $edit->description ?? '') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label text-dark">Keywords</label>
                            <input type="text" name="keywords"
                                class="form-control input-form @error('keywords') is-invalid @enderror"
                                placeholder="Enter keywords separated by commas (e.g., organic, fresh, local)"
                                value="{{ old('keywords', isset($edit) ? implode(', ', json_decode($edit->keywords ?? '[]', true)) : '') }}">
                            @error('keywords')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                            <small class="text-muted">Separate keywords with commas. These will help customers find your
                                product.</small>
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
        <script src="{{ url('assets/libs/dropzone/min/dropzone.min.js') }}"></script>
        <script src="{{ url('assets/libs/dropify/js/dropify.min.js') }}"></script>
        <script src="{{ url('assets/js/pages/form-fileuploads.init.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const amountInput = document.getElementById('amount');
                const discountInput = document.getElementById('discount_amount');
                const percentageInput = document.getElementById('discount_percentage');

                function calculatePercentage() {
                    const amount = parseFloat(amountInput.value);
                    const discount = parseFloat(discountInput.value);

                    if (!isNaN(amount) && amount > 0 && !isNaN(discount)) {
                        const bacic = (amount - discount);
                        const percentage = (bacic / amount) * 100;
                        percentageInput.value = percentage.toFixed(2);
                    } else {
                        percentageInput.value = '';
                    }
                }

                function validateDiscountAmount() {
                    const amount = parseFloat(amountInput.value);
                    const discount = parseFloat(discountInput.value);

                    if (!isNaN(amount) && !isNaN(discount) && discount > amount) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Discount',
                            text: 'Discount amount cannot be greater than total amount.',
                        });
                        discountInput.value = '';
                        percentageInput.value = '';
                    }
                }

                discountInput.addEventListener('blur', validateDiscountAmount);
                amountInput.addEventListener('input', calculatePercentage);
                discountInput.addEventListener('input', calculatePercentage);
            });
        </script>
        <script>
            var oldSubCategoryId = "{{ old('sub_category_id', $edit->sub_category_id ?? '') }}";

            $('#category-select').on('change', function() {
                var categoryId = $(this).val();
                $('#subcategory-select').html('<option value="">Loading...</option>');

                if (categoryId) {
                    $.ajax({
                        url: "{{ url('/admin/products/get-subcategories') }}/" + encodeURIComponent(
                            categoryId),
                        type: "GET",
                        success: function(res) {
                            let options = '<option value="">Select Sub Category</option>';
                            $.each(res, function(key, subcat) {
                                let selected = (subcat.id == oldSubCategoryId) ? ' selected' : '';
                                options +=
                                    `<option value="${subcat.id}"${selected}>${subcat.name}</option>`;
                            });
                            $('#subcategory-select').html(options);
                        }
                    });
                } else {
                    $('#subcategory-select').html('<option value="">Select Sub Category</option>');
                }
            });

            $(document).ready(function() {
                var categoryId = $('#category-select').val();
                if (categoryId) {
                    $('#category-select').trigger('change');
                }
            });
        </script>
        <script>
            let deletedAdditionalImages = [];

            function removeAdditionalImage(button, imageId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This image will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (imageId) {
                            deletedAdditionalImages.push(imageId);
                            document.getElementById('deleted-additional-images').value = deletedAdditionalImages.join(
                                ',');
                        }

                        button.closest('.existing-image, .new-image').remove();

                        updateFileInputState();

                        Swal.fire({
                            icon: 'success',
                            title: 'Removed!',
                            text: 'The image has been removed.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            }
            document.getElementById('main-images-input').addEventListener('change', function(e) {
                const previewContainer = document.getElementById('main-images-preview');
                previewContainer.innerHTML = ''; // Clear previous image

                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();

                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'col-md-4 mb-2 position-relative new-image';
                    div.innerHTML = `
                        <img src="${e.target.result}"
                            class="img-thumbnail"
                            style="max-height: 80px; width: 100%; object-fit: cover;">
                    `;
                    previewContainer.appendChild(div);
                };

                reader.readAsDataURL(file);
            });
            document.getElementById('additional-images-input').addEventListener('change', function(e) {
                const previewContainer = document.getElementById('additional-images-preview');
                const existingImages = previewContainer.querySelectorAll('.existing-image, .new-image');
                const remainingSlots = 3 - existingImages.length;

                if (this.files.length > remainingSlots) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Upload Limit Reached',
                        html: `You can only upload <b>${remainingSlots}</b> more image${remainingSlots > 1 ? 's' : ''}.<br>You already have <b>${existingImages.length}</b> uploaded.`,
                        confirmButtonText: 'Okay',
                    });
                    this.value = '';
                    return;
                }


                previewContainer.querySelectorAll('.new-image').forEach(el => el.remove());

                Array.from(this.files).forEach((file, index) => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'col-md-4 mb-2 position-relative new-image';
                        div.innerHTML = `
                    <img src="${e.target.result}"
                         class="img-thumbnail"
                         style="max-height: 80px; width: 100%; object-fit: cover;">
                    <button type="button"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-image-btn"
                            onclick="removeAdditionalImage(this)">
                        <i class="fa fa-times"></i>
                    </button>
                `;
                        previewContainer.appendChild(div);
                    };

                    reader.readAsDataURL(file);
                });

                updateFileInputState();
            });

            function getYoutubeEmbedUrl($url)
    {
        if (empty($url)) {
            return '';
        }

        $parsed = parse_url($url);

        if (isset($parsed['host']) && strpos($parsed['host'], 'youtu.be') !== false) {
            $videoId = ltrim($parsed['path'], '/');
            $videoId = explode('?', $videoId)[0];
            return "https://www.youtube.com/embed/{$videoId}";
        }

        if (isset($parsed['host']) && strpos($parsed['host'], 'youtube.com') !== false) {
            parse_str($parsed['query'] ?? '', $query);
            if (isset($query['v'])) {
                return "https://www.youtube.com/embed/{$query['v']}";
            }
        }

        return '';
    }

            function updateFileInputState() {
                const previewContainer = document.getElementById('additional-images-preview');
                const currentImageCount = previewContainer.querySelectorAll('.existing-image, .new-image').length;
                const fileInput = document.getElementById('additional-images-input');

                if (currentImageCount >= 3) {
                    fileInput.disabled = true;
                } else {
                    fileInput.disabled = false;
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                updateFileInputState();
            });
        </script>

        <!-- Additional Information Scripts -->
        <script>
            let infoCounter = {{ !empty($existingInfo) ? count($existingInfo) : 1 }};
            let detailCounter = {};

            function addAdditionalInfo() {
                const container = document.getElementById('additional-info-container');
                const newItem = document.createElement('div');
                newItem.className = 'additional-info-item border rounded p-3 mb-3';
                newItem.innerHTML = `
                    <div class="row">
                        <div class="col-md-10 mb-3">
                            <label class="form-label text-dark">Information Heading</label>
                            <input type="text" name="additional_info[${infoCounter}][heading]"
                                class="form-control input-form"
                                placeholder="e.g., Features, Materials, Specifications"
                                value="">
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-info-btn" onclick="removeAdditionalInfo(this)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="info-details-container">
                        <div class="row info-detail-item mb-2">
                            <div class="col-md-5">
                                <label class="form-label text-dark">Title</label>
                                <input type="text" name="additional_info[${infoCounter}][items][0][title]"
                                    class="form-control input-form"
                                    placeholder="e.g., Brand Name, Color, Size"
                                    value="">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label text-dark">Value</label>
                                <input type="text" name="additional_info[${infoCounter}][items][0][value]"
                                    class="form-control input-form"
                                    placeholder="e.g., Vega, Red, Large"
                                    value="">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeInfoDetail(this)">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary add-detail-btn" onclick="addInfoDetail(this)">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                `;

                container.appendChild(newItem);
                infoCounter++;
            }

            function removeAdditionalInfo(button) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This information section will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.closest('.additional-info-item').remove();
                        Swal.fire({
                            icon: 'success',
                            title: 'Removed!',
                            text: 'The information section has been removed.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            }

            function addInfoDetail(button) {
                const infoItem = button.closest('.additional-info-item');
                const detailsContainer = infoItem.querySelector('.info-details-container');
                const headingInput = infoItem.querySelector('input[name*="[heading]"]');
                const nameMatch = headingInput.name.match(/additional_info\[(\d+)\]/);

                if (!nameMatch) return;

                const index = nameMatch[1];
                const detailItems = detailsContainer.querySelectorAll('.info-detail-item');
                const itemIndex = detailItems.length;

                const newDetail = document.createElement('div');
                newDetail.className = 'row info-detail-item mb-2';
                newDetail.innerHTML = `
                    <div class="col-md-5">
                        <label class="form-label text-dark">Title</label>
                        <input type="text" name="additional_info[${index}][items][${itemIndex}][title]"
                            class="form-control input-form"
                            placeholder="e.g., Brand Name, Color, Size"
                            value="">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label text-dark">Value</label>
                        <input type="text" name="additional_info[${index}][items][${itemIndex}][value]"
                            class="form-control input-form"
                            placeholder="e.g., Vega, Red, Large"
                            value="">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeInfoDetail(this)">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                `;

                detailsContainer.appendChild(newDetail);
            }

            function removeInfoDetail(button) {
                const detailItem = button.closest('.info-detail-item');
                const detailsContainer = detailItem.closest('.info-details-container');
                const detailItems = detailsContainer.querySelectorAll('.info-detail-item');

                if (detailItems.length > 1) {
                    detailItem.remove();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cannot Remove',
                        text: 'Each information section must have at least one detail item.',
                        confirmButtonText: 'Okay'
                    });
                }
            }

            // Initialize detail counters for existing items
            document.addEventListener('DOMContentLoaded', function() {
                const infoItems = document.querySelectorAll('.additional-info-item');
                infoItems.forEach((item, index) => {
                    const headingInput = item.querySelector('input[name*="[heading]"]');
                    if (headingInput) {
                        const nameMatch = headingInput.name.match(/additional_info\[(\d+)\]/);
                        if (nameMatch) {
                            detailCounter[nameMatch[1]] = item.querySelectorAll('.info-detail-item').length;
                        }
                    }
                });
            });
        </script>
        <script>
            let youtubeVideoCounter = {{ !empty($existingVideos) ? count($existingVideos) : 0 }};

            function addYoutubeVideo() {
                const container = document.getElementById('youtube-videos-container');
                const videoItems = container.querySelectorAll('.youtube-video-item');

                // Check max limit (5 videos)
                if (videoItems.length >= 5) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Maximum Videos Reached',
                        text: 'You can add maximum 5 YouTube videos.',
                        confirmButtonText: 'Okay'
                    });
                    return;
                }

                const newItem = document.createElement('div');
                newItem.className = 'youtube-video-item border rounded p-3 mb-3';
                newItem.innerHTML = `
            <div class="row">
                <div class="col-md-10 mb-3">
                    <label class="form-label text-dark">YouTube Video URL <span class="text-danger">*</span></label>
                    <input type="url"
                           name="youtube_videos[${youtubeVideoCounter}][url]"
                           class="form-control input-form youtube-url-input"
                           placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID"
                           value=""
                           required>
                    <div class="invalid-feedback d-none youtube-url-error">
                        Please enter a valid YouTube URL
                    </div>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="button"
                            class="btn btn-danger remove-youtube-btn"
                            onclick="removeYoutubeVideo(this)">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>

        `;

                container.appendChild(newItem);

                // Add event listener for URL change
                const urlInput = newItem.querySelector('.youtube-url-input');
                urlInput.addEventListener('blur', function() {
                    validateAndPreviewYoutubeVideo(this);
                });

                youtubeVideoCounter++;
            }

            function removeYoutubeVideo(button) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This video will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.closest('.youtube-video-item').remove();
                        youtubeVideoCounter--;
                        Swal.fire({
                            icon: 'success',
                            title: 'Removed!',
                            text: 'The video has been removed.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            }

            function validateAndPreviewYoutubeVideo(input) {
                const url = input.value.trim();
                const errorElement = input.nextElementSibling;
                const videoItem = input.closest('.youtube-video-item');
                const previewElement = videoItem.querySelector('.video-preview');

                // Reset state
                input.classList.remove('is-invalid', 'is-valid');
                errorElement.classList.add('d-none');
                previewElement.classList.add('d-none');

                if (!url) {
                    input.classList.add('is-invalid');
                    errorElement.textContent = 'YouTube URL is required';
                    errorElement.classList.remove('d-none');
                    return;
                }

                // Validate YouTube URL pattern
                const youtubePattern = /^(https?\:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/;
                if (!youtubePattern.test(url)) {
                    input.classList.add('is-invalid');
                    errorElement.textContent = 'Please enter a valid YouTube URL';
                    errorElement.classList.remove('d-none');
                    return;
                }

                // If valid, show preview
                input.classList.add('is-valid');

                // Extract video ID and create embed URL
                const videoId = extractYoutubeVideoId(url);
                if (videoId) {
                    const embedUrl = `https://www.youtube.com/embed/${videoId}`;
                    const iframe = previewElement.querySelector('iframe');
                    iframe.src = embedUrl;
                    previewElement.classList.remove('d-none');
                }
            }

            function extractYoutubeVideoId(url) {
                let videoId = '';

                // Handle youtu.be URLs
                if (url.includes('youtu.be/')) {
                    videoId = url.split('youtu.be/')[1];
                    if (videoId.includes('?')) {
                        videoId = videoId.split('?')[0];
                    }
                }
                // Handle youtube.com URLs
                else if (url.includes('youtube.com')) {
                    const urlObj = new URL(url);
                    videoId = urlObj.searchParams.get('v');
                }

                return videoId;
            }

            // Add event listeners to existing YouTube URL inputs
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.youtube-url-input').forEach(input => {
                    input.addEventListener('blur', function() {
                        validateAndPreviewYoutubeVideo(this);
                    });

                    // Trigger validation for existing URLs
                    if (input.value) {
                        validateAndPreviewYoutubeVideo(input);
                    }
                });
            });
        </script>
    @endpush
@endsection
