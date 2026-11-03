@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <style>
            .accordion-button:not(.collapsed) {
                background-color: #f8f9fa;
                color: #333;
                box-shadow: none;
            }

            .accordion-button:focus {
                box-shadow: none;
                border-color: rgba(0, 0, 0, .125);
            }

            .accordion-item {
                border: 1px solid #dee2e6;
                margin-bottom: 10px;
                border-radius: 8px;
                overflow: hidden;
            }

            .info-detail-item {
                padding: 8px 0;
                border-bottom: 1px dashed #dee2e6;
            }

            .info-detail-item:last-child {
                border-bottom: none;
            }

            .detail-title {
                font-weight: 600;
                color: #495057;
                min-width: 150px;
            }

            .detail-value {
                color: #212529;
            }

            .info-badge {
                background: #667eea 0%;
                color: white;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 0.85rem;
                display: inline-block;
                margin-bottom: 10px;
            }
        </style>
    @endpush
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">
                        Product Details
                    </h4>
                    <div class="page-title-right">
                        <a href="{{ url('/admin/products') }}" class="btn add-btn text-white btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Alert -->
        @if (session('status'))
            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show mb-4" role="alert">
                <i class="mdi mdi-check-circle-outline me-2"></i>
                {{ session('status')['message'] }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                    aria-label="Close"></button>
            </div>
        @endif

        <!-- Main Card -->
        <div class="card">
            <div class="card-header background-color-theme custom-header-color text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-white">
                        <i class="mdi mdi-cube-outline me-1"></i> {{ $product->name }}
                    </h5>
                    <div>
                        <span class="badge text-white me-2">
                            ID: {{ $product->product_no }}
                        </span>
                        <button class="btn btn-theme-second-background add-btn text-white" data-bs-toggle="modal"
                            data-bs-target="#addStockModal">
                            <i class="mdi mdi-plus me-1"></i> Add Stock
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- Product Information Column -->
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="mdi mdi-information-outline me-2"></i> Basic Information
                            </h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted small mb-1">Category</h6>
                                    <p class="fw-bold">{{ $product->category->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted small mb-1">Sub Category</h6>
                                    <p class="fw-bold">{{ $product->subCategory->name ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted small mb-1">Status</h6>
                                    <span class="badge {{ $product->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $product->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted small mb-1">Current Stock</h6>
                                    <div class="d-flex align-items-center">
                                        <p class="fw-bold me-2">{{ $product->product_stock }}</p>
                                        @if ($product->product_stock <= $product->max_product_stock_alert)
                                            <span class="badge bg-warning">
                                                <i class="mdi mdi-alert me-1"></i> Low Stock
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted small mb-1">Stock Alert Threshold</h6>
                                    <p class="fw-bold">{{ $product->max_product_stock_alert }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted small mb-1">Original Price</h6>
                                    <p class="fw-bold text-muted">
                                         @if($product->discount_amount)
                                            <del>₹{{ number_format($product->amount, 2) }}</del>
                                        @else
                                            ₹{{ number_format($product->amount, 2) }}
                                        @endif
                                    </p>
                                </div>
                                @if($product->discount_amount)
                                <div class="col-md-6">
                                    <h6 class="text-muted small mb-1">Selling Price</h6>
                                    <p class="fw-bold text-success h5">
                                        ₹{{ number_format($product->discount_amount, 2) }}
                                    </p>
                                </div>
                                @endif
                            </div>

                            @if($product->discount_percentage)
                            <div class="mb-3">
                                <h6 class="text-muted small mb-1">Discount</h6>
                                <p class="fw-bold text-danger">
                                    {{ number_format($product->discount_percentage, 2) }}% off
                                    <small class="text-muted">
                                        (Save ₹{{ number_format($product->amount - $product->discount_amount, 2) }})
                                    </small>
                                </p>
                            </div>
                            @endif

                            <!-- Keywords Section -->
                            @if ($product->keywords && count(json_decode($product->keywords, true)) > 0)
                                <div class="mb-3">
                                    <h6 class="text-muted small mb-1">Keywords</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach (json_decode($product->keywords, true) as $keyword)
                                            <span class="badge bg-light text-dark border">
                                                {{ $keyword }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Description Section -->
                            <div class="mb-3">
                                <h6 class="text-muted small mb-1">Description</h6>
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <p class="mb-0">{{ $product->description ?? 'No description available' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Images Column -->
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="mdi mdi-image-multiple me-2"></i> Product Images
                            </h5>

                            <!-- Main Image -->
                            <div class="mb-4">
                                <h6 class="text-muted small mb-2">Main Product Image</h6>
                                <div class="text-center">
                                    @if ($product->image)
                                        <img src="{{ $product->image ? url('storage/product/' . $product->image) : asset('images/default-product.png') }}"
                                            class="img-thumbnail cursor-pointer"
                                            style="max-width: 300px; max-height: 300px; object-fit: contain;"
                                            alt="{{ $product->name }}" data-bs-toggle="modal" data-bs-target="#imageModal"
                                            data-image-url="{{ url('storage/product/' . $product->image) }}">
                                        <div class="mt-2">
                                            <small class="text-muted">Click image to view full size</small>
                                        </div>
                                    @else
                                        <div class="border rounded p-5 text-center">
                                            <i class="mdi mdi-image-off" style="font-size: 48px; color: #6c757d;"></i>
                                            <p class="text-muted mt-2">No image available</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Additional Images Section -->
                            @if ($product->additionalImages->count() > 0)
                                <div class="mb-3">
                                    <h6 class="text-muted small mb-2">Additional Images
                                        ({{ $product->additionalImages->count() }})</h6>
                                    <div class="row g-2">
                                        @foreach ($product->additionalImages as $image)
                                            <div class="col-md-4 col-6">
                                                <div class="position-relative">
                                                    <img src="{{ $image->image_path ? url('storage/product/' . $image->image_path) : asset('images/default-product.png') }}"
                                                        class="img-thumbnail cursor-pointer w-100"
                                                        style="height: 120px; object-fit: cover;"
                                                        alt="Additional image {{ $loop->iteration }}"
                                                        data-bs-toggle="modal" data-bs-target="#imageModal"
                                                        data-image-url="{{ url('storage/product/' . $image->image_path) }}">
                                                    <div class="text-center mt-1">
                                                        <small class="text-muted">Image {{ $loop->iteration }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- YouTube Videos Section --}}
                @php
                    // Parse YouTube videos from JSON string
                    $youtubeVideos = [];
                    if ($product->youtube_videos) {
                        $youtubeVideos = json_decode($product->youtube_videos, true);
                    }
                @endphp

                @if (!empty($youtubeVideos) && is_array($youtubeVideos))
                    <div class="mt-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="mdi mdi-youtube me-2 text-danger"></i> YouTube Video Links
                            </h5>
                            <span class="badge bg-danger">
                                {{ count($youtubeVideos) }} Links
                            </span>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    @foreach ($youtubeVideos as $index => $video)
                                        @php
                                            $url = $video['url'] ?? '';
                                            $videoId = '';

                                            // Extract video ID for display
                                            if (str_contains($url, 'youtu.be/')) {
                                                $videoId = str_replace('https://youtu.be/', '', $url);
                                                $videoId = explode('?', $videoId)[0];
                                            } elseif (str_contains($url, 'youtube.com/watch?v=')) {
                                                $query = parse_url($url, PHP_URL_QUERY);
                                                parse_str($query, $params);
                                                $videoId = $params['v'] ?? '';
                                            }
                                        @endphp

                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="badge bg-light text-dark rounded-circle">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                </div>
                                                <div>
                                                    @if ($videoId)
                                                        <div class="text-muted small mb-1">
                                                            <i class="mdi mdi-video me-1"></i>
                                                            YouTube Video ID: <code>{{ $videoId }}</code>
                                                        </div>
                                                    @endif
                                                    <a href="{{ $url }}" target="_blank"
                                                        class="text-primary text-decoration-none">
                                                        <i class="mdi mdi-link-variant me-1"></i>
                                                        {{ Str::limit($url, 60) }}
                                                    </a>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="{{ $url }}" target="_blank"
                                                    class="btn btn-sm btn-outline-danger">
                                                    <i class="mdi mdi-youtube me-1"></i> Open
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-5">
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-link-variant-off me-3" style="font-size: 24px;"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">No Video Links Available</h6>
                                    <p class="mb-0">This product doesn't have any YouTube video links.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Additional Information Section (FAQ Style) -->
                @if ($product->additional_info && count(json_decode($product->additional_info, true)) > 0)
                    <div class="mt-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="mdi mdi-file-document-multiple-outline me-2"></i> Additional Information
                            </h5>
                            <span class="info-badge">
                                <i class="mdi mdi-information-outline me-1"></i>
                                {{ count(json_decode($product->additional_info, true)) }} Sections
                            </span>
                        </div>

                        <div class="accordion" id="additionalInfoAccordion">
                            @foreach (json_decode($product->additional_info, true) as $index => $section)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $index }}"
                                            aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                            aria-controls="collapse{{ $index }}">
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-chevron-right me-2 accordion-icon"></i>
                                                <span class="fw-bold">{{ $section['heading'] }}</span>
                                                <span class="badge bg-primary ms-2">
                                                    {{ count($section['items']) }} items
                                                </span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}"
                                        class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                        aria-labelledby="heading{{ $index }}"
                                        data-bs-parent="#additionalInfoAccordion">
                                        <div class="accordion-body">
                                            <div class="row d-none">
                                                @foreach ($section['items'] as $itemIndex => $item)
                                                    <div class="col-md-6 col-lg-4 mb-3">
                                                        <div class="card h-100 border-light">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-start mb-2">
                                                                    <div class="me-2">
                                                                        <span
                                                                            class="badge bg-light text-dark rounded-circle">
                                                                            {{ $itemIndex + 1 }}
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <h6 class="mb-1 text-primary">
                                                                            {{ $item['title'] }}
                                                                        </h6>
                                                                        <p class="mb-0 text-dark">
                                                                            {{ $item['value'] }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Alternative Table View -->
                                            <div class="mt-4 d-none d-lg-block">
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-sm">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th width="40%">Title</th>
                                                                <th width="60%">Value</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($section['items'] as $item)
                                                                <tr>
                                                                    <td class="detail-title">
                                                                        <i
                                                                            class="mdi mdi-check-circle-outline text-success me-2"></i>
                                                                        {{ $item['title'] }}
                                                                    </td>
                                                                    <td class="detail-value">
                                                                        {{ $item['value'] }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mt-5">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-information-outline me-3" style="font-size: 24px;"></i>
                            <div>
                                <h6 class="alert-heading mb-1">No Additional Information</h6>
                                <p class="mb-0">This product doesn't have any additional information sections. Add
                                    information in the edit page.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Stock History Table -->
                <div class="mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="mdi mdi-history me-2"></i> Stock History
                        </h5>
                        <small class="text-muted">Total Records: <span id="totalRecords">0</span></small>
                    </div>

                    <div class="table-responsive table-border">
                        <table id="stock-history-table" class="table table-striped data-table">
                            <thead class="theme-menu-color">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Previous Stock</th>
                                    <th>New Stock</th>
                                    <th>Changed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table content would be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Product Image Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" style="max-height: 70vh; object-fit: contain;"
                        alt="Product Image">
                </div>
                <div class="modal-footer">
                    <a href="#" id="downloadImage" class="btn btn-primary" download>
                        <i class="mdi mdi-download me-1"></i> Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Stock Modal -->
    <div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addStockForm" action="{{ url('admin/products/add-stock/' . Crypt::encrypt($product->id)) }}"
                    method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStockModalLabel">
                            <i class="mdi mdi-plus-circle me-2"></i> Add Stock
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity to Add</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1"
                                required placeholder="Enter quantity">
                            <div class="form-text">
                                Current stock: <strong>{{ $product->product_stock }}</strong>
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert-circle-outline me-2"></i>
                            Adding stock will update the product's inventory and create a history record.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn add-btn text-white">
                            <i class="mdi mdi-check me-1"></i> Add Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('footer_content')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function() {
            const table = $('#stock-history-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('admin/products/stock-history/' . Crypt::encrypt($product->id)) }}",
                columns: [{
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data) {
                            return new Date(data).toLocaleString();
                        }
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'quantity',
                        name: 'quantity',
                        render: function(data) {
                            return `<span class="fw-bold">${data}</span>`;
                        }
                    },
                    {
                        data: 'previous_stock',
                        name: 'previous_stock'
                    },
                    {
                        data: 'new_stock',
                        name: 'new_stock'
                    },
                    {
                        data: 'changed_by',
                        name: 'user.name',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return data || 'System';
                        }
                    },
                ],
                drawCallback: function(settings) {
                    const api = this.api();
                    $('#totalRecords').text(api.rows().count());
                },
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                responsive: true
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#addStockForm').submit(function(e) {
                e.preventDefault();

                const quantity = $('#quantity').val();
                if (quantity < 1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Quantity',
                        text: 'Please enter a valid quantity (minimum 1).'
                    });
                    return;
                }

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('button[type="submit"]').prop('disabled', true)
                            .html(
                                '<span class="spinner-border spinner-border-sm me-1"></span> Adding...'
                                );
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#addStockModal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: 'Stock Updated',
                                text: response.message ||
                                    'Stock has been added successfully!',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: xhr.responseJSON?.message ||
                                'An error occurred. Please try again.'
                        });
                    },
                    complete: function() {
                        $('button[type="submit"]').prop('disabled', false)
                            .html('<i class="mdi mdi-check me-1"></i> Add Stock');
                    }
                });
            });

            $('#addStockModal').on('hidden.bs.modal', function() {
                $('#addStockForm')[0].reset();
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Image Modal
            const imageModal = document.getElementById('imageModal');
            if (imageModal) {
                imageModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const imageUrl = button.getAttribute('data-image-url');
                    const modalImage = document.getElementById('modalImage');
                    const downloadLink = document.getElementById('downloadImage');

                    modalImage.src = imageUrl;
                    downloadLink.href = imageUrl;

                    const filename = imageUrl.split('/').pop();
                    downloadLink.setAttribute('download', filename);
                });
            }

            // Accordion arrow rotation
            const accordionButtons = document.querySelectorAll('.accordion-button');
            accordionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const icon = this.querySelector('.accordion-icon');
                    if (icon) {
                        if (this.classList.contains('collapsed')) {
                            icon.classList.remove('mdi-chevron-down');
                            icon.classList.add('mdi-chevron-right');
                        } else {
                            icon.classList.remove('mdi-chevron-right');
                            icon.classList.add('mdi-chevron-down');
                        }
                    }
                });
            });

            // Initialize accordion icons
            accordionButtons.forEach(button => {
                const icon = button.querySelector('.accordion-icon');
                if (icon && !button.classList.contains('collapsed')) {
                    icon.classList.remove('mdi-chevron-right');
                    icon.classList.add('mdi-chevron-down');
                }
            });
        });
    </script>
@endpush
