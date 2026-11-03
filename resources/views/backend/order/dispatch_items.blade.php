@extends('backend.layout.main')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-0 fw-semibold text-dark">Dispatch Order Items</h4>
                <a href="{{ route('orders.view', ['order' => Crypt::encrypt($order->id)]) }}"
                   class="btn btn-secondary btn-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Order
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Select Items to Dispatch</h5>
                </div>
                <div class="card-body">
                    <form id="dispatchForm">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50px">
                                            <input type="checkbox" id="selectAllItems">
                                        </th>
                                        <th>Product</th>
                                        <th>Shop</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    <!-- Items will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>

                        <div id="selectedItemsContainer" class="mt-4" style="display: none;">
                            <h6 class="mb-3">Selected Items for Dispatch</h6>
                            <div id="selectedItemsList"></div>
                        </div>

                        <div class="mt-4">
                            <button type="button" id="proceedToShippingBtn" class="btn btn-primary" disabled>
                                <i class="mdi mdi-truck-check me-1"></i> Proceed to Shipping
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Order ID:</strong> {{ $order->order_no }}
                    </div>
                    <div class="mb-3">
                        <strong>Customer:</strong> {{ $order->user->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Shipping Address:</strong>
                        <div class="mt-1 small">
                            {{ $order->shippingAddress->full_address ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Total Items:</strong>
                        <span id="totalItemsCount">0</span>
                    </div>
                    <div class="mb-3">
                        <strong>Ready to Ship:</strong>
                        <span id="readyItemsCount">0</span>
                    </div>
                    <div class="mb-3">
                        <strong>Already Shipped:</strong>
                        <span id="shippedItemsCount">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Shipping Modal -->
<div class="modal fade" id="shippingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enter Shipping Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="shippingDetailsForm">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shipping_date" class="form-label">Shipping Date</label>
                            <input type="date" class="form-control" id="shipping_date" name="shipping_date"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>

                    <div id="shippingItemsContainer">
                        <!-- Shipping items will be dynamically added here -->
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmShippingBtn" class="btn btn-primary">
                            <i class="mdi mdi-truck-delivery me-1"></i> Dispatch Items
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedItems = [];
    let couriers = [];

    // Load items for dispatch
    function loadItems() {
        $.ajax({
            url: "{{ route('orders.items-for-dispatch', ['order' => Crypt::encrypt($order->id)]) }}",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    renderItems(response.items);
                    updateSummary(response.items);
                }
            }
        });
    }

    // Load available couriers
    function loadCouriers() {
        $.ajax({
            url: "{{ route('couriers.available') }}",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    couriers = response.couriers;
                }
            }
        });
    }

    // Render items table
    function renderItems(items) {
        let html = '';
        items.forEach(item => {
            html += `
                <tr id="item-row-${item.id}">
                    <td>
                        <input type="checkbox" class="item-checkbox"
                               data-item-id="${item.id}"
                               ${item.can_ship ? '' : 'disabled'}>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${item.product_image}"
                                 class="rounded me-2" width="40" height="40">
                            <div>
                                <div class="fw-medium">${item.product_name}</div>
                                <small class="text-muted">${item.quantity_unit}</small>
                            </div>
                        </div>
                    </td>
                    <td>${item.shop_name}</td>
                    <td>${item.quantity} ${item.quantity_unit}</td>
                    <td>
                        <span class="badge ${item.can_ship ? 'bg-warning' : 'bg-secondary'}">
                            ${item.can_ship ? 'Ready' : 'Cannot Ship'}
                        </span>
                    </td>
                    <td>
                        ${item.can_ship ? `
                            <button type="button" class="btn btn-sm btn-outline-primary add-single-item"
                                    data-item-id="${item.id}">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `;
        });
        $('#itemsContainer').html(html);
    }

    // Update order summary
    function updateSummary(items) {
        const totalItems = items.length;
        const readyItems = items.filter(item => item.can_ship).length;
        const shippedItems = totalItems - readyItems;

        $('#totalItemsCount').text(totalItems);
        $('#readyItemsCount').text(readyItems);
        $('#shippedItemsCount').text(shippedItems);
    }

    // Toggle select all items
    $('#selectAllItems').change(function() {
        const isChecked = $(this).prop('checked');
        $('.item-checkbox:not(:disabled)').prop('checked', isChecked).trigger('change');
    });

    // Handle item selection
    $(document).on('change', '.item-checkbox', function() {
        const itemId = $(this).data('item-id');
        const isChecked = $(this).prop('checked');

        if (isChecked && !selectedItems.includes(itemId)) {
            selectedItems.push(itemId);
        } else if (!isChecked) {
            selectedItems = selectedItems.filter(id => id !== itemId);
        }

        updateSelectedItemsList();
        toggleProceedButton();
    });

    // Add single item
    $(document).on('click', '.add-single-item', function() {
        const itemId = $(this).data('item-id');

        if (!selectedItems.includes(itemId)) {
            selectedItems.push(itemId);
            $(`#item-row-${itemId} .item-checkbox`).prop('checked', true);
        }

        updateSelectedItemsList();
        toggleProceedButton();
    });

    // Update selected items list
    function updateSelectedItemsList() {
        if (selectedItems.length > 0) {
            $('#selectedItemsContainer').show();

            let html = '<div class="list-group">';
            selectedItems.forEach(itemId => {
                const row = $(`#item-row-${itemId}`);
                const productName = row.find('td:nth-child(2) .fw-medium').text();
                const quantity = row.find('td:nth-child(4)').text();
                const shopName = row.find('td:nth-child(3)').text();

                html += `
                    <div class="list-group-item" data-item-id="${itemId}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-medium">${productName}</div>
                                <small class="text-muted">${shopName} • ${quantity}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            html += '</div>';

            $('#selectedItemsList').html(html);
        } else {
            $('#selectedItemsContainer').hide();
        }
    }

    // Remove selected item
    $(document).on('click', '.remove-item', function() {
        const itemId = $(this).closest('.list-group-item').data('item-id');
        selectedItems = selectedItems.filter(id => id !== itemId);

        $(`#item-row-${itemId} .item-checkbox`).prop('checked', false);
        updateSelectedItemsList();
        toggleProceedButton();
    });

    // Toggle proceed button
    function toggleProceedButton() {
        $('#proceedToShippingBtn').prop('disabled', selectedItems.length === 0);
    }

    // Proceed to shipping
    $('#proceedToShippingBtn').click(function() {
        openShippingModal();
    });

    // Open shipping modal
    function openShippingModal() {
        // Clear previous content
        $('#shippingItemsContainer').empty();

        // Add shipping form for each selected item
        selectedItems.forEach(itemId => {
            const row = $(`#item-row-${itemId}`);
            const productName = row.find('td:nth-child(2) .fw-medium').text();
            const quantity = row.find('td:nth-child(4)').text();
            const shopName = row.find('td:nth-child(3)').text();

            let courierOptions = '<option value="">Select Courier</option>';
            couriers.forEach(courier => {
                courierOptions += `<option value="${courier.id}">${courier.name} (${courier.estimated_delivery_days} days)</option>`;
            });

            $('#shippingItemsContainer').append(`
                <div class="card mb-3 shipping-item-card" data-item-id="${itemId}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Product</label>
                                <input type="text" class="form-control" value="${productName}" readonly>
                                <input type="hidden" name="items[${itemId}][order_item_id]" value="${itemId}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Shop</label>
                                <input type="text" class="form-control" value="${shopName}" readonly>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="text" class="form-control" value="${quantity}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Courier</label>
                                <select class="form-select courier-select"
                                        name="items[${itemId}][courier_id]" required>
                                    ${courierOptions}
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tracking Number</label>
                                <input type="text" class="form-control tracking-number"
                                       name="items[${itemId}][tracking_number]" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Estimated Days</label>
                                <input type="number" class="form-control estimated-days"
                                       name="items[${itemId}][estimated_days]" min="1" value="3" required>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('shippingModal'));
        modal.show();
    }

    // Auto-update estimated days when courier is selected
    $(document).on('change', '.courier-select', function() {
        const courierId = $(this).val();
        const estimatedDaysInput = $(this).closest('.row').find('.estimated-days');

        if (courierId) {
            const courier = couriers.find(c => c.id == courierId);
            if (courier) {
                estimatedDaysInput.val(courier.estimated_delivery_days);
            }
        }
    });

    // Confirm shipping
    $('#confirmShippingBtn').click(function() {
        const form = $('#shippingDetailsForm');
        const formData = new FormData(form[0]);

        // Validate all fields
        let isValid = true;
        $('.courier-select').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        $('.tracking-number').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            Swal.fire('Error', 'Please fill all required fields', 'error');
            return;
        }

        // Convert FormData to object
        const data = {};
        formData.forEach((value, key) => {
            // Handle array inputs
            if (key.includes('[')) {
                const matches = key.match(/(\w+)\[(\d+)\]\[(\w+)\]/);
                if (matches) {
                    const [, prefix, index, field] = matches;
                    if (!data[prefix]) data[prefix] = {};
                    if (!data[prefix][index]) data[prefix][index] = {};
                    data[prefix][index][field] = value;
                }
            } else {
                data[key] = value;
            }
        });

        // Convert items object to array
        if (data.items) {
            data.items = Object.values(data.items);
        }

        // Show loading
        Swal.fire({
            title: 'Processing...',
            text: 'Dispatching items',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false
        });

        // Submit via AJAX
        $.ajax({
            url: "{{ route('orders.dispatch-items', ['order' => Crypt::encrypt($order->id)]) }}",
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('shippingModal')).hide();

                    // Redirect to order view
                    window.location.href = "{{ route('orders.view', ['order' => Crypt::encrypt($order->id)]) }}";
                });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to dispatch items', 'error');
            }
        });
    });

    // Initialize
    loadItems();
    loadCouriers();
});
</script>
@endpush
