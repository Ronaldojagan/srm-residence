@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
        <link href="{{ url('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <style></style>
    @endpush

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <h4 class="mb-0 fw-semibold text-dark">Order Details</h4>

                        @php
                            $productStatuses = $order->orderItems->pluck('status')->unique();
                            $cancelledItems = $order->orderItems->where('status', 'cancelled')->count();
                            $deliveredItems = $order->orderItems->where('status', 'delivered')->count();
                            $shippedItems = $order->orderItems->where('status', 'shipped')->count();
                            $totalItems = $order->orderItems->count();
                        @endphp

                        <span
                            class="order-view-status-badge badge text-capitalize fw-medium shadow-sm rounded-pill
                            @if ($cancelledItems == $totalItems) bg-danger text-white
                            @elseif($deliveredItems == $totalItems)
                                bg-success text-white
                            @elseif($shippedItems > 0)
                                bg-info text-white
                            @elseif($order->orderItems->where('status', 'pending')->count() > 0)
                                bg-warning text-dark
                            @else
                                bg-secondary text-white @endif">
                            @if ($cancelledItems == $totalItems)
                                Cancelled
                            @elseif($deliveredItems == $totalItems)
                                Completed
                            @elseif($shippedItems > 0)
                                In Progress
                            @else
                                {{ $order->orderItems->first()->status ?? 'Pending' }}
                            @endif
                        </span>

                        <div class="product-status-summary d-flex gap-1">
                            @php
                                $statusCounts = [
                                    'pending' => $order->orderItems->where('status', 'pending')->count(),
                                    'confirmed' => $order->orderItems->where('status', 'confirmed')->count(),
                                    'processing' => $order->orderItems->where('status', 'processing')->count(),
                                    'shipped' => $order->orderItems->where('status', 'shipped')->count(),
                                    'delivered' => $order->orderItems->where('status', 'delivered')->count(),
                                    'cancelled' => $order->orderItems->where('status', 'cancelled')->count(),
                                    'return_requested' => $order->orderItems
                                        ->where('status', 'return_requested')
                                        ->count(),
                                    'replace_requested' => $order->orderItems
                                        ->where('status', 'replace_requested')
                                        ->count(),
                                ];
                            @endphp

                            @foreach ($statusCounts as $status => $count)
                                @if ($count > 0)
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'confirmed' => 'info',
                                            'processing' => 'primary',
                                            'shipped' => 'info',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            'return_requested' => 'warning',
                                            'replace_requested' => 'warning',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }} text-white">
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}: {{ $count }}
                                    </span>
                                @endif
                            @endforeach
                        </div>

                        @if ($order->cancelled_at)
                            <span class="badge bg-light text-dark shadow-sm rounded-pill d-flex align-items-center gap-1">
                                <i class="mdi mdi-clock-alert-outline"></i>
                                Cancelled on {{ $order->cancelled_at->format('d M Y h:i A') }}
                            </span>
                        @endif
                    </div>

                    <div class="page-title-right d-flex flex-wrap justify-content-end align-items-center gap-2">
                        <div class="dropdown">
                            <button
                                class="btn btn-theme-second-success shadow-sm rounded-pill px-3 dropdown-toggle d-flex align-items-center"
                                type="button" id="orderActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-cog me-2"></i> Actions
                            </button>
                            <ul class="dropdown-menu shadow-sm rounded-3" aria-labelledby="orderActionsDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2"
                                        href="{{ url('admin/orders/invoice/' . Crypt::encrypt($order->id)) }}"
                                        target="_blank">
                                        <i class="mdi mdi-file-pdf-box text-danger"></i> Download Invoice
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="#"
                                        id="exportOrderBtn">
                                        <i class="mdi mdi-file-excel text-success"></i> Export Order
                                    </a>
                                </li>

                                {{-- <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="#"
                                        id="bulkStatusUpdateBtn">
                                        <i class="mdi mdi-update text-primary"></i> Update Products Status
                                    </a>
                                </li> --}}

                                @if ($order->orderItems->where('status', '!=', 'delivered')->where('status', '!=', 'cancelled')->where('status', '!=', 'returned')->where('status', '!=', 'refunded')->count() > 0)
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 mark-all-delivered-btn"
                                            href="#" data-order-id="{{ Crypt::encrypt($order->id) }}">
                                            <i class="mdi mdi-check-all text-success"></i> Mark All as Delivered
                                        </a>
                                    </li>
                                @endif

                                @if (
                                    $order->orderItems->where('status', 'confirmed')->count() > 0 ||
                                        $order->orderItems->where('status', 'processing')->count() > 0)
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="#"
                                            id="dispatchOrderBtn">
                                            <i class="mdi mdi-truck-delivery text-primary"></i> Dispatch Order Items
                                        </a>
                                    </li>
                                @endif

                                @if (\App\Enums\OrderStatus::canCancel($order->status))
                                    {{-- <li>
                                        <a class="dropdown-item text-danger d-flex align-items-center gap-2 cancel-order-btn"
                                            href="#" data-id="{{ $order->id }}"
                                            data-amount="{{ $order->total_amount }}"
                                            data-payment-type="{{ $order->payment->payment_method ?? 'COD' }}">
                                            <i class="mdi mdi-cancel"></i> Cancel Order
                                        </a>
                                    </li> --}}
                                @endif
                            </ul>
                        </div>

                        <a href="{{ url('/admin/orders') }}" class="btn add-btn text-white shadow-sm rounded-pill px-3">
                            <i class="mdi mdi-keyboard-backspace me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show mb-4" role="alert">
                <i
                    class="mdi mdi-{{ session('status')['type'] == 'success' ? 'check-circle-outline' : 'alert-circle-outline' }} me-2"></i>
                {{ session('status')['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="card order-view-order-detail-card mb-4">
                    <div class="card-header custom-header-color text-white">
                        <h5 class="mb-0 text-white"><i class="mdi mdi-file-document-outline me-2"></i>Order Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="order-view-order-summary-title"><i class="mdi mdi-account-outline me-2"></i>Customer
                                Information</h6>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <img src="{{ $order->user->image == 'avatar.jpg' ? url('img/avatar.jpg') : ($order->user->image ? url($order->user->image) : url('img/avatar.jpg')) }}"
                                        class="order-view-user-avatar rounded-circle" alt="{{ $order->user->name }}">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">{{ $order->user->name }}</h6>
                                    <p class="mb-1"><i class="mdi mdi-phone-outline me-1"></i>{{ $order->user->phone }}
                                    </p>
                                    <p class="mb-0"><i
                                            class="mdi mdi-email-outline me-1"></i>{{ $order->user->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="mb-4">
                            <h6 class="order-view-order-summary-title"><i class="mdi mdi-home-map-marker me-2"></i>Shipping
                                Address
                            </h6>
                            <address class="mb-0">
                                <strong>{{ $order->shippingAddress->name ?? 'N/A' }}</strong><br>
                                <div class="d-flex align-items-start mb-1 mt-2">
                                    <i class="mdi mdi-map-marker-outline me-2 mt-1"></i>
                                    <div style="white-space: normal; word-break: break-word;">
                                        {{ $order->shippingAddress->full_address ?? 'No shipping address provided' }}
                                    </div>
                                </div>
                                <i class="mdi mdi-phone-outline me-1"></i>
                                {{ $order->shippingAddress->phone ?? 'N/A' }}
                            </address>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="mdi mdi-timeline-clock-outline me-2"></i>Order Timeline</h5>
                        <div class="order-view-timeline-advanced">
                            @foreach ($order->statusHistory as $history)
                                <div class="order-view-timeline-item-advanced">
                                    <div
                                        class="order-view-timeline-marker bg-{{ $history->status == \App\Enums\OrderStatus::COMPLETED
                                            ? 'success'
                                            : ($history->status == \App\Enums\OrderStatus::CANCELLED
                                                ? 'danger'
                                                : ($history->status == \App\Enums\OrderStatus::PROCESSING
                                                    ? 'info'
                                                    : ($history->status == \App\Enums\OrderStatus::SHIPPED
                                                        ? 'primary'
                                                        : 'secondary'))) }}">
                                        @if ($history->status == \App\Enums\OrderStatus::COMPLETED)
                                            <i class="mdi mdi-check"></i>
                                        @elseif($history->status == \App\Enums\OrderStatus::CANCELLED)
                                            <i class="mdi mdi-close"></i>
                                        @elseif($history->status == \App\Enums\OrderStatus::PROCESSING)
                                            <i class="mdi mdi-progress-wrench"></i>
                                        @elseif($history->status == \App\Enums\OrderStatus::SHIPPED)
                                            <i class="mdi mdi-truck-delivery"></i>
                                        @else
                                            <i class="mdi mdi-clock-outline"></i>
                                        @endif
                                    </div>
                                    <div class="order-view-timeline-content">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="order-view-timeline-status">{{ $history->status }}</span>
                                            <small
                                                class="order-view-timeline-date">{{ $history->created_at->format('d M Y h:i A') }}</small>
                                        </div>
                                        @if ($history->notes)
                                            <div class="order-view-timeline-notes">
                                                <i class="mdi mdi-note-text-outline me-1"></i> {{ $history->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($order->status == 'Cancelled' && $order->cancellation_reason)
                            <div class="mt-4 order-view-cancellation-card p-3 rounded">
                                <h6 class="order-view-cancellation-title mb-3"><i
                                        class="mdi mdi-alert-circle-outline me-2"></i>Cancellation Details</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="order-view-order-detail-label">Reason:</span>
                                    <span class="order-view-order-detail-value">{{ $order->cancellation_reason }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="order-view-order-detail-label">Cancelled At:</span>
                                    <span
                                        class="order-view-order-detail-value">{{ $order->cancelled_at->format('d M Y h:i A') }}</span>
                                </div>
                                @if ($order->payment && $order->payment->refund_id)
                                    <div class="d-flex justify-content-between mt-2">
                                        <span class="order-view-order-detail-label">Refund ID:</span>
                                        <span
                                            class="order-view-order-detail-value">{{ $order->payment->refund_id }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="order-view-order-detail-label">Refund Amount:</span>
                                        <span
                                            class="order-view-order-detail-value text-danger">-₹{{ number_format($order->payment->refund_amount, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card order-view-order-detail-card mb-4">
                    <div
                        class="card-header custom-header-color text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white"><i class="mdi mdi-cart-outline me-2"></i>Order Items</h5>
                        <div class="d-flex align-items-center">
                            <span class="badge text-white info-badge">
                                <i class="mdi mdi-calendar-clock me-1"></i>
                                {{ $order->created_at->format('d M Y h:i A') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        {{-- <th width="30px">
                                            <input type="checkbox" id="selectAllItems">
                                        </th> --}}
                                        <th width="50px">#</th>
                                        <th>Product</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Total</th>
                                        <th>Status</th>
                                        <th>Seller/Shop</th>
                                        <th>Dispatch Status</th>
                                        <th>Refund Status</th>
                                        {{-- <th>Actions</th> --}}
                                        <th>Return/Replace</th>
                                        <th>History</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->orderItems as $index => $item)
                                        @php
                                            $itemStatusColors = [
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'processing' => 'primary',
                                                'shipped' => 'info',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                                'return_requested' => 'warning',
                                                'replace_requested' => 'warning',
                                                'returned' => 'secondary',
                                                'refunded' => 'info',
                                            ];

                                            $refundStatusColors = [
                                                'pending' => 'warning',
                                                'processed' => 'success',
                                                'failed' => 'danger',
                                                'not_required' => 'secondary',
                                            ];
                                        @endphp

                                        <tr id="item-row-{{ $item->id }}"
                                            class="{{ $item->status->value == 'cancelled' ? 'table-danger' : '' }}
                                                   {{ $item->status->value == 'return_requested' ? 'table-warning' : '' }}
                                                   {{ $item->status->value == 'replace_requested' ? 'table-info' : '' }}">
                                            {{-- <td>
                                                @if (!$item->shipment && in_array($item->status->value, ['pending', 'confirmed', 'processing']))
                                                    <input type="checkbox" class="order-item-checkbox"
                                                        data-item-id="{{ $item->id }}"
                                                        data-product="{{ $item->product_name }}">
                                                @endif
                                            </td> --}}
                                            <td class="fw-medium">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $item->product_image ? url('storage/product/' . $item->product_image) : url('img/no-image.png') }}"
                                                        class="order-view-product-img-sm rounded me-3"
                                                        alt="{{ $item->product_name }}">
                                                    <div>
                                                        <h6 class="mb-1">{{ $item->product_name }}</h6>
                                                        <small class="text-muted">SKU:
                                                            {{ $item->product->sku ?? 'N/A' }}</small>
                                                        @if ($item->new_product_id && $item->status->value == 'replace_requested')
                                                            <div class="mt-1">
                                                                <small class="text-success">
                                                                    <i class="mdi mdi-autorenew"></i>
                                                                    Replacement:
                                                                    {{ $item->newProduct->name ?? 'New Product' }}
                                                                </small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end fw-medium">₹{{ number_format($item->amount, 2) }}</td>
                                            <td class="text-end">x{{ $item->quantity }}</td>
                                            <td class="text-end fw-bold">₹{{ number_format($item->total_amount, 2) }}</td>

                                            <td>
                                                <span
                                                    class="badge status-badge bg-{{ $itemStatusColors[$item->status->value] ?? 'secondary' }} text-white px-2 py-1">
                                                    {{ ucfirst(str_replace('_', ' ', $item->status->value)) }}
                                                </span>

                                                @if ($item->status->value == 'return_requested')
                                                    <div class="mt-1">
                                                        @if ($item->return_image)
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-info view-return-image-btn"
                                                                data-item-id="{{ $item->id }}"
                                                                data-image-url="{{ $item->return_image_url }}"
                                                                title="View Return Image">
                                                                <i class="mdi mdi-image"></i> View Image
                                                            </button>
                                                        @endif
                                                        @if ($item->return_verified_by_admin === true)
                                                            <span class="badge bg-success mt-1">Admin Approved</span>
                                                        @elseif($item->return_verified_by_admin === false)
                                                            <span class="badge bg-danger mt-1">Admin Rejected</span>
                                                        @endif
                                                    </div>
                                                @endif

                                                @if ($item->status->value == 'replace_requested')
                                                    <div class="mt-1">
                                                        @if ($item->replace_image)
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-info view-replace-image-btn"
                                                                data-item-id="{{ $item->id }}"
                                                                data-image-url="{{ $item->replace_image_url }}"
                                                                title="View Replace Image">
                                                                <i class="mdi mdi-image"></i> View Image
                                                            </button>
                                                        @endif
                                                        @if ($item->replace_verified_by_admin === true)
                                                            <span class="badge bg-success mt-1">Admin Approved</span>
                                                            @if ($item->replacementOrder)
                                                                <div class="mt-1">
                                                                    <a href="{{ route('orders.view', ['order' => Crypt::encrypt($item->replacementOrder->id)]) }}"
                                                                        class="btn btn-sm btn-outline-info">
                                                                        View Replacement Order
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @elseif($item->replace_verified_by_admin === false)
                                                            <span class="badge bg-danger mt-1">Admin Rejected</span>
                                                        @endif
                                                    </div>
                                                @endif

                                                @if ($item->cancelled_at)
                                                    <small class="d-block text-muted mt-1">
                                                        {{ $item->cancelled_at->format('d M Y') }}
                                                    </small>
                                                @endif

                                                @if (!in_array($item->status->value, ['cancelled', 'delivered', 'returned', 'refunded']))
                                                    {{-- <div class="dropdown mt-1">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                            Update Status
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @foreach (\App\Enums\OrderItemStatus::getAll() as $status)
                                                                <!-- Replace this section in the Status column -->
                                                                @if (!in_array($item->status->value, ['cancelled', 'delivered', 'returned', 'refunded']))
                                                                    <div class="dropdown mt-1">
                                                                        <button
                                                                            class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                            type="button" data-bs-toggle="dropdown"
                                                                            aria-expanded="false">
                                                                            Update Status
                                                                        </button>
                                                                        <ul class="dropdown-menu">
                                                                            @foreach (\App\Enums\OrderItemStatus::getAll() as $status)
                                                                                @if ($status != $item->status->value && $item->canTransitionTo($status))
                                                                                    <li>
                                                                                        <a class="dropdown-item update-item-status"
                                                                                            href="#"
                                                                                            data-item-id="{{ $item->id }}"
                                                                                            data-status="{{ $status }}">
                                                                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                                                        </a>
                                                                                    </li>
                                                                                @endif
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    </div> --}}
                                                @endif
                                            </td>

                                            <td>
                                                @if($item->shop)
                                                    <div class="d-flex align-items-center">
                                                        @if($item->shop->image)
                                                            <img src="{{ url('storage/shop/' . $item->shop->image) }}"
                                                                class="rounded-circle me-2"
                                                                style="width: 30px; height: 30px; object-fit: cover;">
                                                        @else
                                                            <div class="rounded-circle bg-light text-center me-2"
                                                                style="width: 30px; height: 30px; line-height: 30px;">
                                                                <i class="mdi mdi-store"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                           <a href="{{ url('admin/shops/' . Crypt::encrypt($item->shop->id)) }}">
                                                                <strong>{{ $item->shop->name }}</strong>
                                                            </a>

                                                            <div class="text-muted small">
                                                                @if($item->shop->phone)
                                                                    <i class="mdi mdi-phone"></i> {{ $item->shop->phone }}
                                                                @endif
                                                            </div>
                                                            <div class="text-muted small">
                                                                @if($item->shop->owner_phone)
                                                                    <i class="mdi mdi-phone"></i> {{ $item->shop->owner_phone }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($item->product && $item->product->shop)
                                                    <div class="d-flex align-items-center">
                                                        @if($item->product->shop->image)
                                                            <img src="{{ url('storage/shop/' . $item->product->shop->image) }}"
                                                                alt="{{ $item->product->shop->name }}"
                                                                class="rounded-circle me-2"
                                                                style="width: 30px; height: 30px; object-fit: cover;">
                                                        @else
                                                            <div class="rounded-circle bg-light text-center me-2"
                                                                style="width: 30px; height: 30px; line-height: 30px;">
                                                                <i class="mdi mdi-store"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <strong>{{ $item->product->shop->name }}</strong>
                                                            <div class="text-muted small">
                                                                @if($item->product->shop->phone)
                                                                    <i class="mdi mdi-phone"></i> {{ $item->product->shop->phone }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="mdi mdi-store-remove"></i> No Shop Assigned
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Dispatch Status Column -->
                                            <td>
                                                @if ($item->shipment)
                                                    @php
                                                        $dispatchColors = [
                                                            'picked_up' => 'info',
                                                            'in_transit' => 'primary',
                                                            'out_for_delivery' => 'warning',
                                                            'delivered' => 'success',
                                                            'failed_attempt' => 'danger',
                                                            'returned' => 'dark',
                                                            'cancelled' => 'danger',
                                                            'delayed' => 'warning',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $dispatchColors[$item->shipment->status->value] ?? 'secondary' }} text-white px-2 py-1">
                                                        {{ \App\Enums\DeliveryStatus::getDescription($item->shipment->status->value) }}
                                                    </span>

                                                    @if ($item->shipment->tracking_number)
                                                        <small class="d-block text-muted mt-1">
                                                            <i class="mdi mdi-truck-fast me-1"></i>
                                                            {{ $item->shipment->tracking_number }}
                                                        </small>
                                                    @endif
                                                    @if ($item->shipment->courier)
                                                        <small class="d-block text-muted">
                                                            <i class="mdi mdi-package-variant me-1"></i>
                                                            {{ $item->shipment->courier->name }}
                                                        </small>
                                                    @endif
                                                    @if ($item->shipment->estimated_delivery_at)
                                                        <small class="d-block text-muted">
                                                            <i class="mdi mdi-calendar-clock me-1"></i>
                                                            Est:
                                                            {{ $item->shipment->estimated_delivery_at->format('d M Y') }}
                                                        </small>
                                                    @endif
                                                    @if ($item->shipment->actual_delivery_at)
                                                        <small class="d-block text-muted">
                                                            <i class="mdi mdi-check-circle me-1"></i>
                                                            Delivered:
                                                            {{ $item->shipment->actual_delivery_at->format('d M Y') }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-light text-muted px-2 py-1">
                                                        <i class="mdi mdi-package-variant-closed me-1"></i>
                                                        Not Dispatched
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Refund Status Column -->
                                            <td>
                                                @if (in_array($item->status->value, ['cancelled', 'returned']) && $order->payment->payment_method == 'Razorpay')
                                                    @if ($item->refund_status == 'processed')
                                                        <span class="badge bg-success text-white px-2 py-1">
                                                            <i class="mdi mdi-check-circle me-1"></i>
                                                            Refunded ₹{{ number_format($item->refund_amount, 2) }}
                                                        </span>
                                                        @if ($item->refund_id)
                                                            <small class="d-block text-muted mt-1">
                                                                ID: {{ $item->refund_id }}
                                                            </small>
                                                        @endif
                                                    @elseif($item->refund_status == 'pending')
                                                        <span class="badge bg-warning text-white px-2 py-1">
                                                            <i class="mdi mdi-clock-outline me-1"></i>
                                                            Refund Pending
                                                        </span>
                                                        {{-- <button
                                                            class="btn btn-sm btn-outline-warning mt-1 process-refund-btn"
                                                            data-item-id="{{ $item->id }}"
                                                            data-product="{{ $item->product_name }}"
                                                            data-max-amount="{{ $item->total_amount }}">
                                                            Process Refund
                                                        </button> --}}
                                                    @else
                                                        <span class="badge bg-secondary text-white px-2 py-1">
                                                            {{ ucfirst($item->refund_status ?? 'Not Required') }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-light text-muted px-2 py-1">-</span>
                                                @endif
                                            </td>

                                            <!-- Actions Column -->
                                            {{-- <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if (!$item->shipment && in_array($item->status->value, ['pending', 'confirmed', 'processing']))
                                                        <button type="button"
                                                            class="btn btn-outline-primary dispatch-item-btn"
                                                            data-item-id="{{ $item->id }}"
                                                            data-product="{{ $item->product_name }}"
                                                            title="Dispatch Item">
                                                            <i class="mdi mdi-truck-fast"></i>
                                                        </button>
                                                    @endif

                                                    @if ($item->shipment)
                                                        <button type="button" class="btn btn-outline-info track-item-btn"
                                                            data-shipment-id="{{ $item->shipment->id }}"
                                                            data-tracking="{{ $item->shipment->tracking_number }}"
                                                            title="Track Shipment">
                                                            <i class="mdi mdi-map-marker-path"></i>
                                                        </button>
                                                    @endif

                                                    @if (!in_array($item->status->value, ['cancelled', 'delivered', 'returned', 'refunded', 'return_requested', 'replace_requested']))
                                                        <button type="button"
                                                            class="btn btn-outline-danger cancel-product-btn"
                                                            data-order-id="{{ $order->id }}"
                                                            data-item-id="{{ $item->id }}"
                                                            data-product="{{ $item->product_name }}"
                                                            data-max-amount="{{ $item->total_amount }}"
                                                            title="Cancel Product">
                                                            <i class="mdi mdi-cancel"></i>
                                                        </button>
                                                    @endif

                                                    @if ($item->status->value == 'cancelled' && $item->refund_status == 'pending' && $order->payment->payment_method == 'Razorpay')
                                                        <button type="button"
                                                            class="btn btn-outline-warning process-refund-btn"
                                                            data-item-id="{{ $item->id }}"
                                                            data-product="{{ $item->product_name }}"
                                                            data-max-amount="{{ $item->total_amount }}"
                                                            title="Process Refund">
                                                            <i class="mdi mdi-cash-refund"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td> --}}

                                            <!-- Return/Replace Column -->
                                            <td>
                                                @if ($item->status->value == 'delivered')
                                                    {{-- <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-warning return-request-btn"
                                                            data-item-id="{{ $item->id }}"
                                                            data-product="{{ $item->product_name }}">
                                                            <i class="mdi mdi-arrow-u-left-top"></i> Return
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-info replace-request-btn"
                                                            data-item-id="{{ $item->id }}"
                                                            data-product="{{ $item->product_name }}">
                                                            <i class="mdi mdi-autorenew"></i> Replace
                                                        </button>
                                                    </div> --}}
                                                    -
                                                @elseif($item->status->value == 'return_requested')
                                                    <div class="verification-flow">
                                                        <div class="verification-step mb-2">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="fw-medium">Admin Verification:</span>
                                                                @if (is_null($item->return_verified_by_admin))
                                                                    <span class="badge bg-warning">
                                                                        <i class="mdi mdi-clock-outline me-1"></i> Pending
                                                                    </span>
                                                                    @if (auth()->user()->type == \App\Enums\UserType::SUPERADMIN)
                                                                        <div class="btn-group btn-group-sm">
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-success admin-verify-return-btn"
                                                                                data-item-id="{{ $item->id }}"
                                                                                data-product="{{ $item->product_name }}">
                                                                                Verify
                                                                            </button>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-danger admin-reject-return-btn"
                                                                                data-item-id="{{ $item->id }}"
                                                                                data-product="{{ $item->product_name }}">
                                                                                Reject
                                                                            </button>
                                                                        </div>
                                                                    @endif
                                                                @elseif($item->return_verified_by_admin === true)
                                                                    <span class="badge bg-success">
                                                                        <i class="mdi mdi-check-circle me-1"></i> Verified
                                                                    </span>
                                                                    @if ($item->admin_verification_notes)
                                                                        <small
                                                                            class="text-muted d-block">{{ Str::limit($item->admin_verification_notes, 30) }}</small>
                                                                    @endif
                                                                @else
                                                                    <span class="badge bg-danger">
                                                                        <i class="mdi mdi-close-circle me-1"></i> Rejected
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Step 2: Shop Verification (Only if admin verified) -->
                                                        @if ($item->return_verified_by_admin === true)
                                                            <div class="verification-step mb-2">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <span class="fw-medium">Shop Verification:</span>
                                                                    @if (is_null($item->shop_verified))
                                                                        <span class="badge bg-warning">
                                                                            <i class="mdi mdi-clock-outline me-1"></i>
                                                                            Pending
                                                                        </span>
                                                                        @if (auth()->user()->type == \App\Enums\UserType::SHOP && auth()->user()->shop_id == $item->shop_id)
                                                                            <div class="btn-group btn-group-sm">
                                                                                <button type="button"
                                                                                    class="btn btn-sm btn-outline-success shop-verify-return-btn"
                                                                                    data-item-id="{{ $item->id }}"
                                                                                    data-product="{{ $item->product_name }}">
                                                                                    Verify
                                                                                </button>
                                                                                <button type="button"
                                                                                    class="btn btn-sm btn-outline-danger shop-reject-return-btn"
                                                                                    data-item-id="{{ $item->id }}"
                                                                                    data-product="{{ $item->product_name }}">
                                                                                    Reject
                                                                                </button>
                                                                            </div>
                                                                        @endif
                                                                    @elseif($item->shop_verified === true)
                                                                        <span class="badge bg-success">
                                                                            <i class="mdi mdi-store-check me-1"></i>
                                                                            Verified
                                                                        </span>
                                                                        @if ($item->product_condition)
                                                                            <small class="text-muted d-block">Condition:
                                                                                {{ $item->product_condition }}</small>
                                                                        @endif
                                                                    @else
                                                                        <span class="badge bg-danger">
                                                                            <i class="mdi mdi-store-remove me-1"></i>
                                                                            Rejected
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- Step 3: Admin Final Approval (Only if shop verified) -->
                                                            @if ($item->shop_verified === true)
                                                                <div class="verification-step mb-2">
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center">
                                                                        <span class="fw-medium">Final Approval:</span>
                                                                        @if (is_null($item->return_approval))
                                                                            <span class="badge bg-warning">
                                                                                <i class="mdi mdi-clock-outline me-1"></i>
                                                                                Pending
                                                                            </span>
                                                                            @if (auth()->user()->type == \App\Enums\UserType::SUPERADMIN)
                                                                                <div class="btn-group btn-group-sm">
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-outline-success admin-approve-return-btn"
                                                                                        data-item-id="{{ $item->id }}"
                                                                                        data-product="{{ $item->product_name }}">
                                                                                        Approve
                                                                                    </button>
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-outline-danger admin-reject-final-return-btn"
                                                                                        data-item-id="{{ $item->id }}"
                                                                                        data-product="{{ $item->product_name }}">
                                                                                        Reject
                                                                                    </button>
                                                                                </div>
                                                                            @endif
                                                                        @elseif($item->return_approval === true)
                                                                            <span class="badge bg-success">
                                                                                <i class="mdi mdi-check-all me-1"></i>
                                                                                Approved
                                                                            </span>
                                                                            @if ($item->status->value == 'returned')
                                                                                <!-- Refund Status -->
                                                                                <div class="mt-1">
                                                                                    @if ($item->refund_status == 'processed')
                                                                                        <span class="badge bg-success">
                                                                                            <i
                                                                                                class="mdi mdi-cash-check me-1"></i>
                                                                                            Refunded
                                                                                        </span>
                                                                                    @else
                                                                                        <button type="button"
                                                                                            class="btn btn-sm btn-outline-warning process-return-refund-btn"
                                                                                            data-item-id="{{ $item->id }}"
                                                                                            data-product="{{ $item->product_name }}">
                                                                                            <i
                                                                                                class="mdi mdi-cash-refund"></i>
                                                                                            Process Refund
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                        @else
                                                                            <span class="badge bg-danger">
                                                                                <i class="mdi mdi-close-circle me-1"></i>
                                                                                Rejected
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @elseif($item->status->value == 'replace_requested')
                                                    <!-- Replace Request Status -->
                                                    <div class="verification-flow">
                                                        <!-- Step 1: Admin Verification -->
                                                        <div class="verification-step mb-2">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="fw-medium">Admin Verification:</span>
                                                                @if (is_null($item->replace_verified_by_admin))
                                                                    <span class="badge bg-warning">
                                                                        <i class="mdi mdi-clock-outline me-1"></i> Pending
                                                                    </span>
                                                                    @if (auth()->user()->type == \App\Enums\UserType::SUPERADMIN)
                                                                        <div class="btn-group btn-group-sm">
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-success admin-verify-replace-btn"
                                                                                data-item-id="{{ $item->id }}"
                                                                                data-product="{{ $item->product_name }}">
                                                                                Verify
                                                                            </button>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-danger admin-reject-replace-btn"
                                                                                data-item-id="{{ $item->id }}"
                                                                                data-product="{{ $item->product_name }}">
                                                                                Reject
                                                                            </button>
                                                                        </div>
                                                                    @endif
                                                                @elseif($item->replace_verified_by_admin === true)
                                                                    <span class="badge bg-success">
                                                                        <i class="mdi mdi-check-circle me-1"></i> Verified
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-danger">
                                                                        <i class="mdi mdi-close-circle me-1"></i> Rejected
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Step 2: Shop Verification (Only if admin verified) -->
                                                        @if ($item->replace_verified_by_admin === true)
                                                            <div class="verification-step mb-2">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <span class="fw-medium">Shop Verification:</span>
                                                                    @if (is_null($item->shop_verified))
                                                                        <span class="badge bg-warning">
                                                                            <i class="mdi mdi-clock-outline me-1"></i>
                                                                            Pending
                                                                        </span>
                                                                        @if (auth()->user()->type == \App\Enums\UserType::SHOP && auth()->user()->shop_id == $item->shop_id)
                                                                            <div class="btn-group btn-group-sm">
                                                                                <button type="button"
                                                                                    class="btn btn-sm btn-outline-success shop-verify-replace-btn"
                                                                                    data-item-id="{{ $item->id }}"
                                                                                    data-product="{{ $item->product_name }}">
                                                                                    Verify
                                                                                </button>
                                                                                <button type="button"
                                                                                    class="btn btn-sm btn-outline-danger shop-reject-replace-btn"
                                                                                    data-item-id="{{ $item->id }}"
                                                                                    data-product="{{ $item->product_name }}">
                                                                                    Reject
                                                                                </button>
                                                                            </div>
                                                                        @endif
                                                                    @elseif($item->shop_verified === true)
                                                                        <span class="badge bg-success">
                                                                            <i class="mdi mdi-store-check me-1"></i>
                                                                            Verified
                                                                        </span>
                                                                        @if ($item->product_condition)
                                                                            <small class="text-muted d-block">Condition:
                                                                                {{ $item->product_condition }}</small>
                                                                        @endif
                                                                    @else
                                                                        <span class="badge bg-danger">
                                                                            <i class="mdi mdi-store-remove me-1"></i>
                                                                            Rejected
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- Step 3: Admin Final Approval (Only if shop verified) -->
                                                            @if ($item->shop_verified === true)
                                                                <div class="verification-step mb-2">
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center">
                                                                        <span class="fw-medium">Final Approval:</span>
                                                                        @if (is_null($item->replace_approval))
                                                                            <span class="badge bg-warning">
                                                                                <i class="mdi mdi-clock-outline me-1"></i>
                                                                                Pending
                                                                            </span>
                                                                            @if (auth()->user()->type == \App\Enums\UserType::SUPERADMIN)
                                                                                <div class="btn-group btn-group-sm">
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-outline-success admin-approve-replace-btn"
                                                                                        data-item-id="{{ $item->id }}"
                                                                                        data-product="{{ $item->product_name }}">
                                                                                        Approve
                                                                                    </button>
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-outline-danger admin-reject-final-replace-btn"
                                                                                        data-item-id="{{ $item->id }}"
                                                                                        data-product="{{ $item->product_name }}">
                                                                                        Reject
                                                                                    </button>
                                                                                </div>
                                                                            @endif
                                                                        @elseif($item->replace_approval === true)
                                                                            <span class="badge bg-success">
                                                                                <i class="mdi mdi-check-all me-1"></i>
                                                                                Approved
                                                                            </span>
                                                                            @if ($item->replacementOrder)
                                                                                <div class="mt-1">
                                                                                    <a href="{{ route('orders.view', ['order' => Crypt::encrypt($item->replacementOrder->id)]) }}"
                                                                                        class="btn btn-sm btn-outline-info">
                                                                                        <i class="mdi mdi-eye me-1"></i>
                                                                                        View Replacement
                                                                                    </a>
                                                                                </div>
                                                                            @endif
                                                                        @else
                                                                            <span class="badge bg-danger">
                                                                                <i class="mdi mdi-close-circle me-1"></i>
                                                                                Rejected
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @elseif($item->status->value == 'returned')
                                                    <span class="badge bg-secondary">
                                                        <i class="mdi mdi-check-circle me-1"></i> Returned
                                                    </span>
                                                    @if ($item->refund_amount)
                                                        <div class="mt-1">
                                                            <small>Refunded:
                                                                ₹{{ number_format($item->refund_amount, 2) }}</small>
                                                        </div>
                                                    @endif
                                                @elseif($item->status->value === 'replaced')
                                                    <span class="badge bg-secondary">
                                                        <i class="mdi mdi-check-circle me-1"></i> Replaced
                                                    </span>
                                                    @if ($item->replacementOrder)
                                                        <div class="mt-1">
                                                            <a href="{{ url('admin/orders/'. Crypt::encrypt($item->replacementOrder->id)) }}"
                                                                class="btn btn-sm btn-outline-info">
                                                                <i class="mdi mdi-eye me-1"></i>
                                                                View Replacement
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endif
                                            </td>

                                            <!-- History Column -->
                                            <td>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-info view-history-btn"
                                                    data-item-id="{{ Crypt::encrypt($item->id) }}"
                                                    data-product="{{ $item->product_name }}" title="View History">
                                                    <i class="mdi mdi-history"></i>
                                                </button>
                                            </td>

                                            <!-- Rating Column -->
                                            <td>
                                                @php
                                                    $rating = \App\Models\Feedback::where('order_id', $order->id)
                                                        ->where('product_id', $item->product_id)
                                                        ->pluck('rating')
                                                        ->first();
                                                @endphp
                                                @if ($rating)
                                                    <div class="rateyo-readonly"
                                                        data-rateyo-rating="{{ $rating }}"
                                                        data-rateyo-star-width="20px" data-rateyo-spacing="2px"></div>
                                                @else
                                                    <span class="badge bg-light text-muted">Not Rated</span>
                                                @endif
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

        <div class="row">
            <div class="col-lg-12">
                <div class="card order-view-order-detail-card mb-4">
                    <div
                        class="card-header custom-header-color text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white"><i class="mdi mdi-cart-outline me-2"></i>Order Payment</h5>
                        <div class="d-flex align-items-center">
                            <span class="badge text-white info-badge">
                                <i class="mdi mdi-calendar-clock me-1"></i>
                                {{ $order->created_at->format('d M Y h:i A') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="order-view-order-summary-card p-2 h-100">
                                    <h6 class="order-view-order-summary-title"><i
                                            class="mdi mdi-information-outline me-2"></i>Order
                                        Summary</h6>
                                    <div class="order-view-order-detail-row mb-2">
                                        <span class="order-view-order-detail-label">Order ID:</span>
                                        <span class="order-view-order-detail-value">{{ $order->order_no }}</span>
                                    </div>
                                    <div class="order-view-order-detail-row mb-2">
                                        <span class="order-view-order-detail-label">Order Type:</span>
                                        <span
                                            class="order-view-order-detail-value">{{ $order->is_replacement_order ? 'Replacement Order' : 'Regular Order' }}</span>
                                    </div>
                                    @if ($order->is_replacement_order && $order->original_order_id)
                                        <div class="order-view-order-detail-row mb-2">
                                            <span class="order-view-order-detail-label">Replacement For:</span>
                                            <span class="order-view-order-detail-value">
                                                <a
                                                    href="{{ url('orders'. Crypt::encrypt($order->original_order_id)) }}">
                                                    Order #{{ $order->originalOrder->order_no ?? 'N/A' }}
                                                </a>
                                            </span>
                                        </div>
                                    @endif
                                    @if($order->payment)
                                    <div class="order-view-order-detail-row mb-2">
                                        <span class="order-view-order-detail-label">Payment Status:</span>
                                        <span
                                            class="badge bg-{{ $order->payment->status == 'Paid' ? 'success' : ($order->payment->status == 'Refund' ? 'danger' : 'warning') }} text-white px-1 py-1">
                                            {{ ucfirst($order->payment->status) }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <!-- In the col-md-6 section for Payment Summary -->
                            <div class="col-md-6">
                                <div class="order-view-order-summary-card p-3 h-100">
                                    <h6 class="order-view-order-summary-title mb-3">
                                        <i class="mdi mdi-cash-multiple me-2"></i>Payment Summary
                                    </h6>

                                    <!-- Original Order Amounts -->
                                    <div class="order-view-order-detail-row mb-2">
                                        <span class="order-view-order-detail-label">Subtotal:</span>
                                        <span class="order-view-order-detail-value">
                                            ₹{{ number_format($order->precheckout->sub_total ?? $order->total_amount, 2) }}
                                        </span>
                                    </div>

                                    @if ($order->precheckout && $order->precheckout->coupon_amount > 0)
                                        <div class="order-view-order-detail-row mb-2">
                                            <span class="order-view-order-detail-label">Coupon Discount:</span>
                                            <span class="order-view-order-detail-value text-danger">
                                                -₹{{ number_format($order->precheckout->coupon_amount, 2) }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($order->precheckout && $order->precheckout->delivery_charge > 0)
                                        <div class="order-view-order-detail-row mb-2">
                                            <span class="order-view-order-detail-label">Delivery Charge:</span>
                                            <span class="order-view-order-detail-value">
                                                ₹{{ number_format($order->precheckout->delivery_charge, 2) }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($order->precheckout && $order->precheckout->platform_fee > 0)
                                        <div class="order-view-order-detail-row mb-2">
                                            <span class="order-view-order-detail-label">Platform Fee:</span>
                                            <span class="order-view-order-detail-value">
                                                ₹{{ number_format($order->precheckout->platform_fee, 2) }}
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Calculate refund totals -->
                                    @php
                                        $cancelledRefundTotal = $order->orderItems
                                            ->where('status', 'cancelled')
                                            ->where('refund_status', 'processed')
                                            ->sum('refund_amount');

                                        $returnedRefundTotal = $order->orderItems
                                            ->where('status', 'returned')
                                            ->where('refund_status', 'processed')
                                            ->sum('refund_amount');

                                        $totalRefunded = $cancelledRefundTotal + $returnedRefundTotal;

                                        $pendingRefunds = $order->orderItems
                                            ->whereIn('status', ['cancelled', 'returned'])
                                            ->where('refund_status', 'pending')
                                            ->sum('total_amount');
                                    @endphp

                                    <!-- Show Original Total -->
                                    <div class="order-view-order-detail-row mt-3 pt-2 border-top">
                                        <span class="order-view-order-detail-label fw-bold">Original Total:</span>
                                        <strong class="h6 text-primary">
                                            ₹{{ number_format($order->total_amount, 2) }}
                                        </strong>
                                    </div>

                                    @php
                                        $cancelledItems = $order->orderItems->filter(function($item) {
                                            return $item->status->value == 'cancelled';
                                        });

                                        $returnedItems = $order->orderItems->filter(function($item) {
                                            return $item->status->value == 'returned' && $item->refund_status == 'processed';
                                        });

                                        $cancelledRefundTotal = $cancelledItems->sum('total_amount');
                                        $returnedRefundTotal = $returnedItems->sum('refund_amount');

                                        $totalRefunded = $cancelledRefundTotal + $returnedRefundTotal;
                                        $finalAmount = $order->total_amount - $totalRefunded;
                                    @endphp

                                    @if ($totalRefunded > 0)
                                        <div class="refund-summary-section mt-3 pt-3 border-top">
                                            <h6 class="order-view-order-summary-title mb-2 text-danger">
                                                <i class="mdi mdi-cash-refund me-2"></i>Refund Summary
                                            </h6>

                                            @if ($cancelledRefundTotal > 0)
                                                <div class="order-view-order-detail-row mb-2">
                                                    <span class="order-view-order-detail-label">Cancelled Items Refund:</span>
                                                    <span class="order-view-order-detail-value text-danger">
                                                        -₹{{ number_format($cancelledRefundTotal, 2) }}
                                                    </span>
                                                </div>
                                            @endif

                                            @if ($returnedRefundTotal > 0)
                                                <div class="order-view-order-detail-row mb-2">
                                                    <span class="order-view-order-detail-label">Returned Items Refund:</span>
                                                    <span class="order-view-order-detail-value text-danger">
                                                        -₹{{ number_format($returnedRefundTotal, 2) }}
                                                    </span>
                                                </div>
                                            @endif

                                            <!-- Final Amount After Refunds -->
                                            <div class="order-view-order-detail-row mt-2 pt-2 border-top">
                                                <span class="order-view-order-detail-label fw-bold">Final Amount:</span>
                                                <strong class="h5 {{ $finalAmount == 0 ? 'text-success' : 'text-primary' }}">
                                                    ₹{{ number_format($finalAmount, 2) }}
                                                </strong>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div class="mt-4 p-2 order-view-order-summary-card">
                                <h6 class="order-view-order-summary-title"><i
                                        class="mdi mdi-credit-card-outline me-2"></i>Payment
                                    Information</h6>
                                <div class="order-view-order-detail-row mb-2">
                                    <span class="order-view-order-detail-label">Method:</span>
                                    <span
                                        class="order-view-order-detail-value">{{ ucfirst($order->payment->payment_method ?? 'N/A') }}</span>
                                </div>
                                @if ($order->payment && $order->payment->payment_method != 'COD')
                                    <div class="order-view-order-detail-row mb-2">
                                        <span class="order-view-order-detail-label">Transaction ID:</span>
                                        <span
                                            class="order-view-order-detail-value">{{ $order->payment->transaction_id ?? 'N/A' }}</span>
                                    </div>
                                @endif
                                @if ($order->payment && $order->payment->refund_id)
                                    <div class="order-view-order-detail-row mb-2">
                                        <span class="order-view-order-detail-label">Refund ID:</span>
                                        <span
                                            class="order-view-order-detail-value">{{ $order->payment->refund_id ?? 'N/A' }}</span>
                                    </div>
                                    <div class="order-view-order-detail-row mb-2">
                                        <span class="order-view-order-detail-label">Refund Amount:</span>
                                        <span
                                            class="order-view-order-detail-value text-danger">-₹{{ number_format($order->payment->refund_amount, 2) ?? 'N/A' }}</span>
                                    </div>
                                @endif
                                @if($order->payment)
                                <div class="order-view-order-detail-row">
                                    <span class="order-view-order-detail-label">Status:</span>
                                    <span
                                        class="badge bg-{{ $order->payment->status == 'Paid' ? 'success' : ($order->payment->status == 'Refund' ? 'danger' : 'warning') }} text-white px-1 py-1">
                                        {{ ucfirst($order->payment->status) }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Activities Log -->
        {{-- <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header custom-header-color text-white">
                        <h5 class="mb-0 text-white"><i class="mdi mdi-history me-2"></i>Order Activities</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Activity</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activities as $activity)
                                        <tr>
                                            <td>{{ $activity->created_at->format('d M Y h:i A') }}</td>
                                            <td>{{ $activity->description }}</td>
                                            <td>{{ $activity->causer->name ?? 'System' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No activities found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="cancelOrderForm">
                        @csrf
                        <input type="hidden" name="order_id" id="cancelOrderId">
                        <div class="mb-3">
                            <label for="cancellation_reason" class="form-label">Reason for Cancellation</label>
                            <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Refund Amount</label>
                            <input type="text" class="form-control" id="refundAmountDisplay" readonly>
                            <input type="hidden" name="refund_amount" id="refundAmount">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmCancel" required>
                                <label class="form-check-label" for="confirmCancel">
                                    I confirm that I want to cancel this order
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn">Cancel Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Product Modal -->
    <div class="modal fade" id="cancelProductModal" tabindex="-1" aria-labelledby="cancelProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelProductModalLabel">Cancel Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="cancelProductForm">
                        @csrf
                        <input type="hidden" name="order_id" id="cancelProductOrderId">
                        <input type="hidden" name="item_id" id="cancelProductItemId">

                        <div class="mb-3">
                            <label for="product_cancellation_reason" class="form-label">Reason for Cancellation</label>
                            <textarea class="form-control" id="product_cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="product_refund_amount" class="form-label">Refund Amount (₹)</label>
                            <input type="number" class="form-control" id="product_refund_amount" name="refund_amount"
                                step="0.01" min="0" required>
                            <small class="text-muted">Maximum: ₹<span id="maxRefundAmount">0.00</span></small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmProductCancel" required>
                                <label class="form-check-label" for="confirmProductCancel">
                                    I confirm to cancel this product
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelProductBtn">Cancel Product</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Status History Modal -->
    <div class="modal fade" id="productHistoryModal" tabindex="-1" aria-labelledby="productHistoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productHistoryModalLabel">Product Status History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="productHistoryContent">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading history...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Partial Refund Modal -->
    <div class="modal fade" id="partialRefundModal" tabindex="-1" aria-labelledby="partialRefundModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="partialRefundModalLabel">Process Partial Refund</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="partialRefundForm">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ Crypt::encrypt($order->id) }}">
                        <input type="hidden" name="item_id" id="refundItemId">

                        <div class="mb-3">
                            <label for="refund_product_name" class="form-label">Product</label>
                            <input type="text" class="form-control" id="refund_product_name" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="partial_refund_amount" class="form-label">Refund Amount (₹)</label>
                            <input type="number" class="form-control" id="partial_refund_amount" name="refund_amount"
                                step="0.01" min="0" required>
                        </div>

                        <div class="mb-3">
                            <label for="refund_reason" class="form-label">Refund Reason</label>
                            <textarea class="form-control" id="refund_reason" name="refund_reason" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmPartialRefund" required>
                                <label class="form-check-label" for="confirmPartialRefund">
                                    I confirm to process this refund
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="confirmPartialRefundBtn">Process Refund</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Item Status Modal -->
    <div class="modal fade" id="updateItemStatusModal" tabindex="-1" aria-labelledby="updateItemStatusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateItemStatusModalLabel">Update Product Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateItemStatusForm">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ Crypt::encrypt($order->id) }}">
                        <input type="hidden" name="item_id" id="updateItemId">
                        <input type="hidden" name="status" id="updateItemStatus">

                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" id="updateProductName" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <input type="text" class="form-control" id="currentItemStatus" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <input type="text" class="form-control" id="newItemStatus" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="item_status_notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="item_status_notes" name="notes" rows="3"
                                placeholder="Add any notes about this status change..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmItemStatusUpdate">
                        <i class="mdi mdi-update me-1"></i> Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Status Update Modal -->
    <div class="modal fade" id="bulkStatusUpdateModal" tabindex="-1" aria-labelledby="bulkStatusUpdateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkStatusUpdateModalLabel">Update Multiple Products Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bulkStatusUpdateForm">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ Crypt::encrypt($order->id) }}">
                        <div id="bulkUpdateItemsContainer">
                            <!-- Selected items will be added here -->
                        </div>

                        <div class="mb-3">
                            <label for="bulk_status" class="form-label">Select New Status</label>
                            <select class="form-select" id="bulk_status" name="status" required>
                                <option value="">Select Status</option>
                                @foreach (\App\Enums\OrderItemStatus::getAll() as $status)
                                    @if (!in_array($status, ['cancelled', 'delivered', 'returned', 'refunded']))
                                        <option value="{{ $status }}">
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="bulk_status_notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="bulk_status_notes" name="notes" rows="3"
                                placeholder="Add notes for all selected items..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmBulkStatusUpdate">
                        <i class="mdi mdi-update me-1"></i> Update All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Dispatch Item Modal -->
    <div class="modal fade" id="dispatchItemModal" tabindex="-1" aria-labelledby="dispatchItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dispatchItemModalLabel">Dispatch Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="dispatchItemForm">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ Crypt::encrypt($order->id) }}">
                        <input type="hidden" name="item_id" id="dispatchItemId">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dispatch_product_name" class="form-label">Product</label>
                                <input type="text" class="form-control" id="dispatch_product_name" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="dispatch_quantity" class="form-label">Quantity</label>
                                <input type="text" class="form-control" id="dispatch_quantity" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="courier_id" class="form-label">Courier *</label>
                                <select class="form-select" id="courier_id" name="courier_id" required>
                                    <option value="">Select Courier</option>
                                    <!-- Options will be loaded via AJAX -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tracking_number" class="form-label">Tracking Number *</label>
                                <input type="text" class="form-control" id="tracking_number" name="tracking_number"
                                    required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estimated_days" class="form-label">Estimated Delivery Days *</label>
                                <input type="number" class="form-control" id="estimated_days" name="estimated_days"
                                    min="1" value="3" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_date" class="form-label">Shipping Date *</label>
                                <input type="date" class="form-control" id="shipping_date" name="shipping_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="dispatch_notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="dispatch_notes" name="notes" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmDispatch" required>
                                <label class="form-check-label" for="confirmDispatch">
                                    I confirm that this product has been handed over to the courier
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmDispatchBtn">
                        <i class="mdi mdi-truck-check me-1"></i> Dispatch Product
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Dispatch Modal -->
    <div class="modal fade" id="bulkDispatchModal" tabindex="-1" aria-labelledby="bulkDispatchModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkDispatchModalLabel">Dispatch Multiple Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bulkDispatchForm">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ Crypt::encrypt($order->id) }}">

                        <div class="mb-4">
                            <h6>Selected Items for Dispatch:</h6>
                            <div id="selectedItemsList" class="list-group mb-3">
                                <!-- Items will be dynamically added -->
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bulk_courier_id" class="form-label">Courier *</label>
                                <select class="form-select" id="bulk_courier_id" name="courier_id" required>
                                    <option value="">Select Courier</option>
                                    <!-- Options will be loaded via AJAX -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bulk_shipping_date" class="form-label">Shipping Date *</label>
                                <input type="date" class="form-control" id="bulk_shipping_date" name="shipping_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bulk_notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="bulk_notes" name="notes" rows="2"></textarea>
                        </div>

                        <h6>Tracking Numbers:</h6>
                        <div id="trackingNumbersContainer">
                            <!-- Tracking number fields will be dynamically added -->
                        </div>

                        <div class="mb-3 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmBulkDispatch" required>
                                <label class="form-check-label" for="confirmBulkDispatch">
                                    I confirm all products have been handed over to the courier
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmBulkDispatchBtn">
                        <i class="mdi mdi-truck-check me-1"></i> Dispatch All Items
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tracking Modal -->
    <div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trackingModalLabel">Shipment Tracking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="trackingContent">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading tracking information...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="markDeliveredBtn" style="display: none;">
                        <i class="mdi mdi-check-circle me-1"></i> Mark as Delivered
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Request Modal -->
    <div class="modal fade" id="returnRequestModal" tabindex="-1" aria-labelledby="returnRequestModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnRequestModalLabel">Request Return</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="returnRequestForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="item_id" id="returnRequestItemId">

                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" id="returnRequestProductName" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="return_reason" class="form-label">Return Reason *</label>
                            <select class="form-select" id="return_reason" name="return_reason" required>
                                <option value="">Select Reason</option>
                                <option value="Damaged Product">Damaged Product</option>
                                <option value="Wrong Item Received">Wrong Item Received</option>
                                <option value="Product Not as Described">Product Not as Described</option>
                                <option value="Size/Color Issue">Size/Color Issue</option>
                                <option value="Quality Issues">Quality Issues</option>
                                <option value="Changed Mind">Changed Mind</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="return_reason_details" class="form-label">Additional Details (Optional)</label>
                            <textarea class="form-control" id="return_reason_details" name="return_reason_details" rows="3"
                                placeholder="Please provide more details about your return..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="return_image" class="form-label">Return Image (Optional)</label>
                            <input type="file" class="form-control" id="return_image" name="return_image"
                                accept="image/*">
                            <small class="text-muted">Upload image of damaged/wrong product (Max: 5MB)</small>
                            <div class="mt-2" id="imagePreview"></div>
                        </div>

                        <div class="alert alert-info">
                            <i class="mdi mdi-information-outline me-2"></i>
                            Return requests will be reviewed by our team within 24-48 hours.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="submitReturnRequestBtn">
                        <i class="mdi mdi-arrow-u-left-top me-1"></i> Submit Return Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Replace Request Modal -->
    <div class="modal fade" id="replaceRequestModal" tabindex="-1" aria-labelledby="replaceRequestModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replaceRequestModalLabel">Request Replace</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="replaceRequestForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="item_id" id="replaceRequestItemId">

                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" id="replaceRequestProductName" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="replace_reason" class="form-label">Replace Reason *</label>
                            <select class="form-select" id="replace_reason" name="replace_reason" required>
                                <option value="">Select Reason</option>
                                <option value="Damaged Product">Damaged Product</option>
                                <option value="Wrong Item Received">Wrong Item Received</option>
                                <option value="Size/Color Issue">Size/Color Issue</option>
                                <option value="Quality Issues">Quality Issues</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="replace_reason_details" class="form-label">Additional Details (Optional)</label>
                            <textarea class="form-control" id="replace_reason_details" name="replace_reason_details" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="replace_image" class="form-label">Replace Image (Optional)</label>
                            <input type="file" class="form-control" id="replace_image" name="replace_image"
                                accept="image/*">
                            <small class="text-muted">Upload image of damaged/wrong product (Max: 5MB)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" id="submitReplaceRequestBtn">
                        <i class="mdi mdi-autorenew me-1"></i> Submit Replace Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Return Image Modal -->
    <div class="modal fade" id="viewReturnImageModal" tabindex="-1" aria-labelledby="viewReturnImageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReturnImageModalLabel">Return Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="returnImageDisplay" src="" class="img-fluid rounded"
                        style="max-height: 70vh; max-width: 100%;" alt="Return Image">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="downloadReturnImageBtn" class="btn btn-primary" download>
                        <i class="mdi mdi-download me-1"></i> Download Image
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- View Replace Image Modal -->
    <div class="modal fade" id="viewReplaceImageModal" tabindex="-1" aria-labelledby="viewReplaceImageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReplaceImageModalLabel">Replace Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="replaceImageDisplay" src="" class="img-fluid rounded"
                        style="max-height: 70vh; max-width: 100%;" alt="Replace Image">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="downloadReplaceImageBtn" class="btn btn-primary" download>
                        <i class="mdi mdi-download me-1"></i> Download Image
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
        <script src="{{ url('assets/libs/select2/js/select2.min.js') }}"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function() {
                // Initialize rateYo for ratings
                $(".rateyo-readonly").each(function() {
                    $(this).rateYo({
                        rating: $(this).data('rateyo-rating'),
                        starWidth: $(this).data('rateyo-star-width'),
                        spacing: $(this).data('rateyo-spacing'),
                        readOnly: true,
                        ratedFill: "#ffc107",
                        normalFill: "#e9ecef"
                    });
                });

                // Checkbox handling
                $('#selectAllItems').change(function() {
                    const isChecked = $(this).prop('checked');
                    $('.order-item-checkbox').prop('checked', isChecked);
                });

                $(document).on('change', '.order-item-checkbox', function() {
                    const allChecked = $('.order-item-checkbox').length === $('.order-item-checkbox:checked')
                        .length;
                    $('#selectAllItems').prop('checked', allChecked);
                });

                // ========== RETURN REQUEST ==========
                $(document).on('click', '.return-request-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    $('#returnRequestItemId').val(itemId);
                    $('#returnRequestProductName').val(productName);
                    $('#returnRequestModalLabel').text(`Request Return: ${productName}`);

                    // Reset form
                    $('#returnRequestForm')[0].reset();
                    $('#imagePreview').empty();

                    const modal = new bootstrap.Modal(document.getElementById('returnRequestModal'));
                    modal.show();
                });

                // Image preview for return request
                $('#return_image').change(function() {
                    const file = this.files[0];
                    if (file) {
                        if (file.size > 5 * 1024 * 1024) {
                            alert('File size should be less than 5MB');
                            $(this).val('');
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#imagePreview').html(`
                                <div class="border rounded p-2">
                                    <img src="${e.target.result}" class="img-thumbnail" style="max-height: 150px;">
                                    <small class="d-block text-muted mt-1">Preview</small>
                                </div>
                            `);
                        }
                        reader.readAsDataURL(file);
                    } else {
                        $('#imagePreview').empty();
                    }
                });

                // Submit Return Request
                $('#submitReturnRequestBtn').click(function() {
                    if (!$('#return_reason').val()) {
                        Swal.fire('Error', 'Please select a return reason', 'error');
                        return;
                    }

                    const formData = new FormData($('#returnRequestForm')[0]);

                    Swal.fire({
                        title: 'Submit Return Request?',
                        text: 'Your return request will be reviewed by our team',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Submit Request'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('/admin/orders/submit-return-request') }}",
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                beforeSend: function() {
                                    Swal.showLoading();
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                        $('#returnRequestModal').modal('hide');
                                        setTimeout(() => {
                                            location.reload();
                                        }, 2000);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error',
                                        'An error occurred while submitting return request',
                                        'error');
                                }
                            });
                        }
                    });
                });

                $(document).on('click', '.admin-verify-return-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    Swal.fire({
                        title: 'Verify Return Request',
                        html: `
            <div class="text-start">
                <p><strong>Product:</strong> ${productName}</p>
                <label for="admin_refund_amount" class="form-label">Refund Amount (₹)</label>
                <input type="number" class="form-control" id="admin_refund_amount"
                       placeholder="Enter refund amount" step="0.01" min="0" required>
                <label for="admin_verification_notes" class="form-label mt-2">Verification Notes (Optional)</label>
                <textarea class="form-control" id="admin_verification_notes"
                          rows="3" placeholder="Add verification notes..."></textarea>
            </div>
        `,
                        showCancelButton: true,
                        confirmButtonText: 'Verify Request',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        preConfirm: () => {
                            const amount = $('#admin_refund_amount').val();
                            if (!amount || amount <= 0) {
                                Swal.showValidationMessage('Please enter a valid refund amount');
                                return false;
                            }
                            return {
                                amount: amount,
                                notes: $('#admin_verification_notes').val()
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.return.admin-verify') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'approve',
                                    notes: result.value.notes,
                                    refund_amount: result.value.amount
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => location.reload(), 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to verify return request',
                                        'error');
                                }
                            });
                        }
                    });
                });

                // Step 1: Admin Reject Return
                $(document).on('click', '.admin-reject-return-btn', function() {
                    const itemId = $(this).data('item-id');

                    Swal.fire({
                        title: 'Reject Return Request',
                        html: `
            <label for="rejection_reason" class="form-label">Rejection Reason</label>
            <textarea class="form-control" id="rejection_reason"
                      rows="3" placeholder="Enter reason for rejection..." required></textarea>
        `,
                        showCancelButton: true,
                        confirmButtonText: 'Reject Request',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        preConfirm: () => {
                            const reason = $('#rejection_reason').val();
                            if (!reason) {
                                Swal.showValidationMessage('Please enter rejection reason');
                                return false;
                            }
                            return reason;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.return.admin-verify') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'reject',
                                    notes: result.value
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => location.reload(), 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to reject return request',
                                        'error');
                                }
                            });
                        }
                    });
                });

                // Step 2: Shop Verify Return Product
                $(document).on('click', '.shop-verify-return-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    Swal.fire({
                        title: 'Verify Product Condition',
                        html: `
            <div class="text-start">
                <p><strong>Product:</strong> ${productName}</p>
                <label for="product_condition" class="form-label">Product Condition *</label>
                <select class="form-select" id="product_condition" required>
                    <option value="">Select Condition</option>
                    <option value="Like New">Like New</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Damaged">Damaged</option>
                </select>
                <label for="shop_verification_notes" class="form-label mt-2">Shop Notes (Optional)</label>
                <textarea class="form-control" id="shop_verification_notes"
                          rows="3" placeholder="Add verification notes..."></textarea>
            </div>
        `,
                        showCancelButton: true,
                        confirmButtonText: 'Verify Product',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        preConfirm: () => {
                            const condition = $('#product_condition').val();
                            if (!condition) {
                                Swal.showValidationMessage('Please select product condition');
                                return false;
                            }
                            return {
                                condition: condition,
                                notes: $('#shop_verification_notes').val()
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.return.shop-verify') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'approve',
                                    product_condition: result.value.condition,
                                    notes: result.value.notes
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => location.reload(), 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to verify product', 'error');
                                }
                            });
                        }
                    });
                });

                // Step 3: Admin Final Approval for Return
                $(document).on('click', '.admin-approve-return-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    Swal.fire({
                        title: 'Approve Return',
                        html: `
            <div class="text-start">
                <p><strong>Product:</strong> ${productName}</p>
                <p>Admin verified: ✓</p>
                <p>Shop verified: ✓</p>
                <label for="final_approval_notes" class="form-label mt-2">Approval Notes (Optional)</label>
                <textarea class="form-control" id="final_approval_notes"
                          rows="3" placeholder="Add approval notes..."></textarea>
            </div>
        `,
                        showCancelButton: true,
                        confirmButtonText: 'Approve Return',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.return.admin-approve') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'approve',
                                    notes: $('#final_approval_notes').val()
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => location.reload(), 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to approve return', 'error');
                                }
                            });
                        }
                    });
                });

                // ========== REPLACE VERIFICATION FLOW ==========

                // Step 1: Admin Verify Replace Request
                $(document).on('click', '.admin-verify-replace-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    // Get available products for replacement
                    $.ajax({
                        url: "{{ route('orders.available-for-replacement') }}",
                        type: 'GET',
                        data: {
                            item_id: itemId,
                            _token: "{{ csrf_token() }}"
                        },
                        beforeSend: function() {
                            Swal.showLoading();
                        },
                        success: function(response) {
                            Swal.hideLoading();

                            let productOptions = '<option value="">Same Product</option>';
                            if (response.success && response.products.length > 0) {
                                response.products.forEach(product => {
                                    productOptions +=
                                        `<option value="${product.id}">${product.name} (SKU: ${product.sku})</option>`;
                                });
                            }

                            Swal.fire({
                                title: 'Verify Replace Request',
                                html: `
                    <div class="text-start">
                        <p class="d-none"><strong>Original Product:</strong> ${productName}</p>

<label for="new_product_name" class="form-label">Product Name</label>
<input
    type="text"
    class="form-control"
    id="new_product_name"
    value="${productName}"
    readonly
>

                        <small class="text-muted">Select a different product if needed</small>
                        <label for="replace_admin_notes" class="form-label mt-2">Verification Notes (Optional)</label>
                        <textarea class="form-control" id="replace_admin_notes"
                                  rows="3" placeholder="Add verification notes..."></textarea>
                    </div>
                `,
                                showCancelButton: true,
                                confirmButtonText: 'Verify Request',
                                confirmButtonColor: '#17a2b8',
                                cancelButtonColor: '#6c757d',
                                preConfirm: () => {
                                    return {
                                        new_product_id: $('#new_product_id').val(),
                                        notes: $('#replace_admin_notes').val()
                                    };
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: "{{ route('orders.replace.admin-verify') }}",
                                        type: 'POST',
                                        data: {
                                            _token: "{{ csrf_token() }}",
                                            item_id: itemId,
                                            action: 'approve',
                                            new_product_id: result.value
                                                .new_product_id,
                                            notes: result.value.notes
                                        },
                                        beforeSend: function() {
                                            Swal.showLoading();
                                        },
                                        success: function(response) {
                                            Swal.hideLoading();
                                            if (response.success) {
                                                Swal.fire({
                                                    title: 'Success!',
                                                    text: response
                                                        .message,
                                                    icon: 'success',
                                                    showConfirmButton: false,
                                                    timer: 1500
                                                });
                                                setTimeout(() => location
                                                    .reload(), 1500);
                                            } else {
                                                Swal.fire('Error', response
                                                    .message, 'error');
                                            }
                                        },
                                        error: function(xhr) {
                                            Swal.hideLoading();
                                            Swal.fire('Error',
                                                'Failed to verify replace request',
                                                'error');
                                        }
                                    });
                                }
                            });
                        },
                        error: function() {
                            Swal.hideLoading();
                            Swal.fire('Error', 'Failed to load products', 'error');
                        }
                    });
                });

                // Step 1: Admin Reject Replace Request
                $(document).on('click', '.admin-reject-replace-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    Swal.fire({
                        title: 'Reject Replace Request',
                        html: `
            <div class="text-start">
                <p><strong>Product:</strong> ${productName}</p>
                <label for="replace_rejection_reason" class="form-label">Rejection Reason</label>
                <textarea class="form-control" id="replace_rejection_reason"
                          rows="3" placeholder="Enter reason for rejection..." required></textarea>
            </div>
        `,
                        showCancelButton: true,
                        confirmButtonText: 'Reject Request',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        preConfirm: () => {
                            const reason = $('#replace_rejection_reason').val();
                            if (!reason) {
                                Swal.showValidationMessage('Please enter rejection reason');
                                return false;
                            }
                            return reason;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.replace.admin-verify') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'reject',
                                    notes: result.value
                                },
                                beforeSend: function() {
                                    Swal.showLoading();
                                },
                                success: function(response) {
                                    Swal.hideLoading();
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => location.reload(), 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.hideLoading();
                                    Swal.fire('Error', 'Failed to reject replace request',
                                        'error');
                                }
                            });
                        }
                    });
                });

                // Step 2: Admin Final Approval for Replace
                $(document).on('click', '.admin-approve-replace-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    // First, get the selected replacement product info
                    $.ajax({
                        url: "{{ route('orders.replace.get-details') }}",
                        type: 'GET',
                        data: {
                            item_id: itemId,
                            _token: "{{ csrf_token() }}"
                        },
                        beforeSend: function() {
                            Swal.showLoading();
                        },
                        success: function(response) {
                            Swal.hideLoading();

                            let newProductInfo = '';
                            if (response.success && response.new_product) {
                                newProductInfo = `
                    <p><strong>Replacement Product:</strong> ${response.new_product.name}</p>
                    <p><strong>SKU:</strong> ${response.new_product.sku}</p>
                `;
                            }

                            Swal.fire({
                                title: 'Final Approval - Replace',
                                html: `
                    <div class="text-start">
                        <p><strong>Original Product:</strong> ${productName}</p>
                        ${newProductInfo}
                        <p>✓ Admin Verification: Complete</p>
                        <p>✓ Shop Verification: Complete</p>
                        <label for="replace_final_notes" class="form-label mt-2">Final Approval Notes (Optional)</label>
                        <textarea class="form-control" id="replace_final_notes"
                                  rows="3" placeholder="Add approval notes..."></textarea>
                    </div>
                `,
                                showCancelButton: true,
                                confirmButtonText: 'Approve Replace',
                                confirmButtonColor: '#17a2b8',
                                cancelButtonColor: '#6c757d',
                                preConfirm: () => {
                                    return {
                                        notes: $('#replace_final_notes').val()
                                    };
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: "{{ route('orders.replace.admin-approve') }}",
                                        type: 'POST',
                                        data: {
                                            _token: "{{ csrf_token() }}",
                                            item_id: itemId,
                                            action: 'approve',
                                            notes: result.value.notes
                                        },
                                        beforeSend: function() {
                                            Swal.showLoading();
                                        },
                                        success: function(response) {
                                            Swal.hideLoading();
                                            if (response.success) {
                                                Swal.fire({
                                                    title: 'Success!',
                                                    text: response
                                                        .message,
                                                    icon: 'success',
                                                    showConfirmButton: false,
                                                    timer: 2000
                                                });
                                                setTimeout(() => location
                                                    .reload(), 2000);
                                            } else {
                                                Swal.fire('Error', response
                                                    .message, 'error');
                                            }
                                        },
                                        error: function(xhr) {
                                            Swal.hideLoading();
                                            Swal.fire('Error',
                                                'Failed to approve replace',
                                                'error');
                                        }
                                    });
                                }
                            });
                        },
                        error: function() {
                            Swal.hideLoading();
                            Swal.fire('Error', 'Failed to load details', 'error');
                        }
                    });
                });

                // Step 2: Admin Reject Final Approval for Replace
                $(document).on('click', '.admin-reject-final-replace-btn', function() {
                    const itemId = $(this).data('item-id');

                    Swal.fire({
                        title: 'Reject Final Approval - Replace',
                        html: `
            <label for="replace_final_rejection" class="form-label">Rejection Reason</label>
            <textarea class="form-control" id="replace_final_rejection"
                      rows="3" placeholder="Enter reason for rejection..." required></textarea>
        `,
                        showCancelButton: true,
                        confirmButtonText: 'Reject Approval',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        preConfirm: () => {
                            const reason = $('#replace_final_rejection').val();
                            if (!reason) {
                                Swal.showValidationMessage('Please enter rejection reason');
                                return false;
                            }
                            return reason;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.replace.admin-approve') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'reject',
                                    notes: result.value
                                },
                                beforeSend: function() {
                                    Swal.showLoading();
                                },
                                success: function(response) {
                                    Swal.hideLoading();
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => location.reload(), 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.hideLoading();
                                    Swal.fire('Error', 'Failed to reject approval',
                                        'error');
                                }
                            });
                        }
                    });
                });

                // Additional JavaScript for shop reject return (if needed)
                $(document).on('click', '.shop-reject-return-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    Swal.fire({
                        title: 'Reject Product Verification',
                        html: `
            <div class="text-start">
                <p><strong>Product:</strong> ${productName}</p>
                <label for="shop_rejection_reason" class="form-label">Rejection Reason *</label>
                <textarea class="form-control" id="shop_rejection_reason"
                          rows="3" placeholder="Why are you rejecting this product?" required></textarea>
            </div>
        `,
                        showCancelButton: true,
                        confirmButtonText: 'Reject Product',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        preConfirm: () => {
                            const reason = $('#shop_rejection_reason').val();
                            if (!reason) {
                                Swal.showValidationMessage('Please enter rejection reason');
                                return false;
                            }
                            return reason;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.return.shop-verify') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'reject',
                                    notes: result.value
                                },
                                beforeSend: function() {
                                    Swal.showLoading();
                                },
                                success: function(response) {
                                    Swal.hideLoading();
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => location.reload(), 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.hideLoading();
                                    Swal.fire('Error', 'Failed to reject product', 'error');
                                }
                            });
                        }
                    });
                });

                // Admin Reject Final Approval for Return
                $(document).on('click', '.admin-reject-final-return-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    Swal.fire({
                        title: 'Reject Final Approval - Return',
                        html: `
            <div class="text-start">
                <p><strong>Product:</strong> ${productName}</p>
                <label for="final_return_rejection" class="form-label">Rejection Reason *</label>
                <textarea class="form-control" id="final_return_rejection"
                          rows="3" placeholder="Enter reason for rejection..." required></textarea>
            </div>
        `,
                        showCancelButton: true,
                        confirmButtonText: 'Reject Approval',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        preConfirm: () => {
                            const reason = $('#final_return_rejection').val();
                            if (!reason) {
                                Swal.showValidationMessage('Please enter rejection reason');
                                return false;
                            }
                            return reason;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.return.admin-approve') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId,
                                    action: 'reject',
                                    notes: result.value
                                },
                                beforeSend: function() {
                                    Swal.showLoading();
                                },
                                success: function(response) {
                                    Swal.hideLoading();
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => location.reload(), 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.hideLoading();
                                    Swal.fire('Error', 'Failed to reject approval',
                                        'error');
                                }
                            });
                        }
                    });
                });

                // Process Return Refund (after final approval)
                $(document).on('click', '.process-return-refund-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    Swal.fire({
                        title: 'Process Refund',
                        html: `
            <div class="text-start">
                <p><strong>Product:</strong> ${productName}</p>
                <p>✓ Admin Verification: Complete</p>
                <p>✓ Shop Verification: Complete</p>
                <p>✓ Final Approval: Complete</p>
                <p class="text-success">Ready for refund processing</p>
            </div>
        `,
                        showCancelButton: true,
                        confirmButtonText: 'Process Refund',
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.return.process-refund') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    item_id: itemId
                                },
                                beforeSend: function() {
                                    Swal.showLoading();
                                },
                                success: function(response) {
                                    Swal.hideLoading();
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => location.reload(), 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.hideLoading();
                                    Swal.fire('Error', 'Failed to process refund', 'error');
                                }
                            });
                        }
                    });
                });

                // View Replacement Order Details
                $(document).on('click', '.view-replacement-btn', function() {
                    const orderId = $(this).data('order-id');
                    window.open("{{ url('admin/orders') }}/" + orderId, '_blank');
                });

                // ========== UTILITY FUNCTIONS ==========

                // Show verification status details
                $(document).on('click', '.view-verification-details', function() {
                    const itemId = $(this).data('item-id');

                    $.ajax({
                        url: "{{ route('orders.get-details') }}",
                        type: 'GET',
                        data: {
                            item_id: itemId,
                            _token: "{{ csrf_token() }}"
                        },
                        beforeSend: function() {
                            Swal.showLoading();
                        },
                        success: function(response) {
                            Swal.hideLoading();
                            if (response.success) {
                                const details = response.details;
                                let html = `
                    <div class="text-start">
                        <h6>Verification Details</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tr>
                                    <th>Step</th>
                                    <th>Status</th>
                                    <th>By</th>
                                    <th>Date</th>
                                </tr>
                `;

                                // Admin Verification
                                html += `
                    <tr>
                        <td>Admin Verification</td>
                        <td>${details.admin_verified ? '<span class="badge bg-success">Verified</span>' :
                            (details.admin_verified === false ? '<span class="badge bg-danger">Rejected</span>' :
                            '<span class="badge bg-warning">Pending</span>')}</td>
                        <td>${details.admin_verifier || 'N/A'}</td>
                        <td>${details.admin_verified_at || 'N/A'}</td>
                    </tr>
                `;

                                // Shop Verification
                                if (details.admin_verified) {
                                    html += `
                        <tr>
                            <td>Shop Verification</td>
                            <td>${details.shop_verified ? '<span class="badge bg-success">Verified</span>' :
                                (details.shop_verified === false ? '<span class="badge bg-danger">Rejected</span>' :
                                '<span class="badge bg-warning">Pending</span>')}</td>
                            <td>${details.shop_verifier || 'N/A'}</td>
                            <td>${details.shop_verified_at || 'N/A'}</td>
                        </tr>
                    `;
                                }

                                // Final Approval
                                if (details.shop_verified) {
                                    html += `
                        <tr>
                            <td>Final Approval</td>
                            <td>${details.return_approval ? '<span class="badge bg-success">Approved</span>' :
                                (details.return_approval === false ? '<span class="badge bg-danger">Rejected</span>' :
                                '<span class="badge bg-warning">Pending</span>')}</td>
                            <td>${details.approver || 'N/A'}</td>
                            <td>${details.return_approval_at || 'N/A'}</td>
                        </tr>
                    `;
                                }

                                html += `
                            </table>
                        </div>
                    </div>
                `;

                                Swal.fire({
                                    title: 'Verification Timeline',
                                    html: html,
                                    width: '800px',
                                    showCloseButton: true,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.hideLoading();
                            Swal.fire('Error', 'Failed to load details', 'error');
                        }
                    });
                });

                // Refresh verification status
                function refreshVerificationStatus(itemId) {
                    const row = $(`#item-row-${itemId}`);
                    if (row.length) {
                        $.ajax({
                            url: "{{ route('orders.get-status') }}",
                            type: 'GET',
                            data: {
                                item_id: itemId,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Update UI with new status
                                    updateVerificationUI(row, response.status);
                                }
                            }
                        });
                    }
                }

                function updateVerificationUI(row, status) {
                    // Update badges and buttons based on status
                    // Implementation depends on your UI structure
                }

                // ========== REPLACE REQUEST ==========
                $(document).on('click', '.replace-request-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    $('#replaceRequestItemId').val(itemId);
                    $('#replaceRequestProductName').val(productName);
                    $('#replaceRequestModalLabel').text(`Request Replace: ${productName}`);

                    const modal = new bootstrap.Modal(document.getElementById('replaceRequestModal'));
                    modal.show();
                });

                // Submit Replace Request
                $('#submitReplaceRequestBtn').click(function() {
                    const formData = new FormData($('#replaceRequestForm')[0]);

                    $.ajax({
                        url: "{{ route('orders.submit-replace-request') }}",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success', response.message, 'success');
                                $('#replaceRequestModal').modal('hide');
                                location.reload();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Failed to submit replace request', 'error');
                        }
                    });
                });


                // ========== VIEW RETURN IMAGE ==========
                $(document).on('click', '.view-return-image-btn', function() {
                    const imageUrl = $(this).data('image-url');

                    $('#returnImageDisplay').attr('src', imageUrl);
                    $('#downloadReturnImageBtn').attr('href', imageUrl);

                    const modal = new bootstrap.Modal(document.getElementById('viewReturnImageModal'));
                    modal.show();
                });

                // ========== VIEW REPLACE IMAGE ==========
                $(document).on('click', '.view-replace-image-btn', function() {
                    const imageUrl = $(this).data('image-url');

                    $('#replaceImageDisplay').attr('src', imageUrl);
                    $('#downloadReplaceImageBtn').attr('href', imageUrl);

                    const modal = new bootstrap.Modal(document.getElementById('viewReplaceImageModal'));
                    modal.show();
                });

                // ========== INDIVIDUAL ITEM STATUS UPDATE ==========
                $(document).on('click', '.update-item-status', function(e) {
                    e.preventDefault();

                    const itemId = $(this).data('item-id');
                    const newStatus = $(this).data('status');
                    const row = $(`#item-row-${itemId}`);
                    const productName = row.find('td:nth-child(3) h6').text().trim();
                    const currentStatus = row.find('.status-badge').text().trim();

                    $('#updateItemId').val(itemId);
                    $('#updateItemStatus').val(newStatus);
                    $('#updateProductName').val(productName);
                    $('#currentItemStatus').val(currentStatus);
                    $('#newItemStatus').val(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                    $('#item_status_notes').val('');

                    const modal = new bootstrap.Modal(document.getElementById('updateItemStatusModal'));
                    modal.show();
                });

                $('#confirmItemStatusUpdate').click(function() {
                    const formData = $('#updateItemStatusForm').serialize();

                    Swal.fire({
                        title: 'Update Product Status?',
                        text: 'This will change the status of this product',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Update'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.update-item-status') }}",
                                type: 'POST',
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        $('#updateItemStatusModal').modal('hide');
                                        setTimeout(() => {
                                            location.reload();
                                        }, 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error',
                                        'An error occurred while updating status',
                                        'error');
                                }
                            });
                        }
                    });
                });

                // ========== BULK STATUS UPDATE ==========
                $('#bulkStatusUpdateBtn').click(function(e) {
                    e.preventDefault();
                    openBulkStatusUpdateModal();
                });

                function openBulkStatusUpdateModal() {
                    const selectedItems = [];
                    $('.order-item-checkbox:checked').each(function() {
                        const itemId = $(this).data('item-id');
                        const row = $(`#item-row-${itemId}`);
                        const productName = $(this).data('product');
                        const currentStatus = row.find('.status-badge').text().trim();

                        if (!row.find('.cancel-product-btn').length) {
                            selectedItems.push({
                                id: itemId,
                                name: productName,
                                currentStatus: currentStatus
                            });
                        }
                    });

                    if (selectedItems.length === 0) {
                        Swal.fire('Info', 'Please select items to update', 'info');
                        return;
                    }

                    updateBulkItemsList(selectedItems);

                    const modal = new bootstrap.Modal(document.getElementById('bulkStatusUpdateModal'));
                    modal.show();
                }

                function updateBulkItemsList(items) {
                    const container = $('#bulkUpdateItemsContainer');
                    container.empty();

                    items.forEach(item => {
                        container.append(`
                            <div class="mb-2">
                                <input type="hidden" name="item_ids[]" value="${item.id}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${item.name}</strong>
                                        <br><small class="text-muted">Current: ${item.currentStatus}</small>
                                    </div>
                                </div>
                            </div>
                        `);
                    });

                    container.append(`
                        <div class="alert alert-info">
                            <i class="mdi mdi-information-outline me-2"></i>
                            Updating ${items.length} product(s)
                        </div>
                    `);
                }

                $('#confirmBulkStatusUpdate').click(function() {
                    if (!$('#bulk_status').val()) {
                        Swal.fire('Error', 'Please select a status', 'error');
                        return;
                    }

                    const formData = $('#bulkStatusUpdateForm').serialize();

                    Swal.fire({
                        title: 'Update Multiple Products?',
                        text: `This will update status for ${$('input[name="item_ids[]"]').length} product(s)`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Update All'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.update-bulk-item-status') }}",
                                type: 'POST',
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        $('#bulkStatusUpdateModal').modal('hide');
                                        setTimeout(() => {
                                            location.reload();
                                        }, 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error',
                                        'An error occurred while updating statuses',
                                        'error');
                                }
                            });
                        }
                    });
                });

                // ========== MARK ALL AS DELIVERED ==========
                $(document).on('click', '.mark-all-delivered-btn', function(e) {
                    e.preventDefault();

                    const orderId = $(this).data('order-id');

                    Swal.fire({
                        title: 'Mark All Products as Delivered?',
                        text: 'This will mark all undelivered products in this order as delivered',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Mark All Delivered'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.mark-all-delivered') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    order_id: orderId
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => {
                                            location.reload();
                                        }, 1500);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'An error occurred', 'error');
                                }
                            });
                        }
                    });
                });

                // ========== DISPATCH SYSTEM ==========
                let selectedDispatchItems = [];
                let availableCouriers = [];

                // Load available couriers
                function loadCouriers() {
                    $.ajax({
                        url: "{{ route('couriers.available') }}",
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                availableCouriers = response.couriers;

                                // Populate courier dropdowns
                                let courierOptions = '<option value="">Select Courier</option>';
                                response.couriers.forEach(courier => {
                                    courierOptions +=
                                        `<option value="${courier.id}" data-days="${courier.estimated_delivery_days}">${courier.name}</option>`;
                                });

                                $('#courier_id').html(courierOptions);
                                $('#bulk_courier_id').html(courierOptions);
                            }
                        },
                        error: function() {
                            console.error('Failed to load couriers');
                        }
                    });
                }
                loadCouriers();

                $('#dispatchOrderBtn').click(function(e) {
                    e.preventDefault();
                    openBulkDispatchModal();
                });

                // Dispatch single item
                $(document).on('click', '.dispatch-item-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    // Get item details from table row
                    const row = $(`#item-row-${itemId}`);
                    const quantity = row.find('td:nth-child(5)').text().trim();
                    $('#dispatchItemForm')[0].reset();

                    $('#dispatchItemId').val(itemId);
                    $('#dispatch_product_name').val(productName);
                    $('#dispatch_quantity').val(quantity);

                    // Reset form
                    $('#shipping_date').val(new Date().toISOString().split('T')[0]);

                    const modal = new bootstrap.Modal(document.getElementById('dispatchItemModal'));
                    modal.show();
                });

                // Confirm single item dispatch
                $('#confirmDispatchBtn').click(function() {
                    if (!$('#confirmDispatch').is(':checked')) {
                        Swal.fire('Error', 'Please confirm dispatch', 'error');
                        return;
                    }

                    const formData = $('#dispatchItemForm').serialize();

                    Swal.fire({
                        title: 'Dispatch Product?',
                        text: 'This will mark the product as shipped',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Dispatch'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.dispatch-item') }}",
                                type: 'POST',
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                        $('#dispatchItemModal').modal('hide');
                                        setTimeout(() => {
                                            location.reload();
                                        }, 2000);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error',
                                        'An error occurred while dispatching', 'error');
                                }
                            });
                        }
                    });
                });

                // Bulk dispatch modal
                function openBulkDispatchModal() {
                    selectedDispatchItems = [];

                    // Find all items that can be dispatched
                    $('.order-item-checkbox:checked').each(function() {
                        const itemId = $(this).data('item-id');
                        const row = $(`#item-row-${itemId}`);
                        const productName = $(this).data('product');
                        const quantity = row.find('td:nth-child(5)').text().trim();

                        if (!row.find('.dispatch-item-btn').length) {
                            // Item is already dispatched
                            return;
                        }

                        selectedDispatchItems.push({
                            id: itemId,
                            name: productName,
                            quantity: quantity
                        });
                    });

                    if (selectedDispatchItems.length === 0) {
                        Swal.fire('Info', 'Please select items for dispatch', 'info');
                        return;
                    }

                    // Update selected items list
                    updateSelectedItemsList();

                    // Update tracking numbers container
                    updateTrackingNumbers();

                    const modal = new bootstrap.Modal(document.getElementById('bulkDispatchModal'));
                    modal.show();
                }

                // Update selected items list in bulk modal
                function updateSelectedItemsList() {
                    const container = $('#selectedItemsList');
                    container.empty();

                    selectedDispatchItems.forEach(item => {
                        container.append(`
                            <div class="list-group-item" data-item-id="${item.id}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-medium">${item.name}</div>
                                        <small class="text-muted">Qty: ${item.quantity}</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-dispatch-item">
                                        <i class="mdi mdi-close"></i>
                                    </button>
                                </div>
                            </div>
                        `);
                    });
                }

                // Update tracking number fields
                function updateTrackingNumbers() {
                    const container = $('#trackingNumbersContainer');
                    container.empty();

                    selectedDispatchItems.forEach((item, index) => {
                        container.append(`
                            <div class="mb-3">
                                <label class="form-label">${item.name} - Tracking Number *</label>
                                <div class="input-group">
                                    <input type="text" class="form-control tracking-number-input"
                                           name="items[${item.id}][tracking_number]"
                                           placeholder="Enter tracking number" required>
                                    <input type="hidden" name="items[${item.id}][order_item_id]" value="${item.id}">
                                </div>
                            </div>
                        `);
                    });
                }

                // Remove item from bulk dispatch
                $(document).on('click', '.remove-dispatch-item', function() {
                    const itemId = $(this).closest('.list-group-item').data('item-id');
                    selectedDispatchItems = selectedDispatchItems.filter(item => item.id != itemId);

                    updateSelectedItemsList();
                    updateTrackingNumbers();
                });

                // Confirm bulk dispatch
                $('#confirmBulkDispatchBtn').click(function() {
                    if (!$('#confirmBulkDispatch').is(':checked')) {
                        Swal.fire('Error', 'Please confirm dispatch', 'error');
                        return;
                    }

                    if (selectedDispatchItems.length === 0) {
                        Swal.fire('Error', 'No items selected for dispatch', 'error');
                        return;
                    }

                    // Validate all tracking numbers
                    let isValid = true;
                    $('.tracking-number-input').each(function() {
                        if (!$(this).val().trim()) {
                            $(this).addClass('is-invalid');
                            isValid = false;
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    if (!isValid) {
                        Swal.fire('Error', 'Please enter all tracking numbers', 'error');
                        return;
                    }

                    // Prepare form data
                    const formData = $('#bulkDispatchForm').serializeArray();

                    Swal.fire({
                        title: 'Dispatch Items?',
                        text: `This will dispatch ${selectedDispatchItems.length} item(s)`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Dispatch All'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.dispatch-bulk') }}",
                                type: 'POST',
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                        $('#bulkDispatchModal').modal('hide');
                                        setTimeout(() => {
                                            location.reload();
                                        }, 2000);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error',
                                        'An error occurred while dispatching', 'error');
                                }
                            });
                        }
                    });
                });

                // Track shipment
                $(document).on('click', '.track-item-btn', function() {
                    const shipmentId = $(this).data('shipment-id');
                    const trackingNumber = $(this).data('tracking');

                    $('#trackingModalLabel').text(`Tracking: ${trackingNumber}`);

                    // Show loading
                    $('#trackingContent').html(`
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading tracking information...</p>
                        </div>
                    `);

                    // Load tracking information
                    $.ajax({
                        url: "{{ url('orders.shipments.tracking', ':shipmentId') }}".replace(
                            ':shipmentId', shipmentId),
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                renderTrackingInfo(response.shipment);
                            } else {
                                $('#trackingContent').html(`
                                    <div class="alert alert-danger">
                                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                                        Failed to load tracking information
                                    </div>
                                `);
                            }
                        },
                        error: function() {
                            $('#trackingContent').html(`
                                <div class="alert alert-danger">
                                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                                    Error loading tracking information
                                </div>
                            `);
                        }
                    });

                    const modal = new bootstrap.Modal(document.getElementById('trackingModal'));
                    modal.show();
                });

                // Render tracking information
                function renderTrackingInfo(shipment) {
                    let html = `
                        <div class="tracking-info">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Shipment Details</h6>
                                            <p class="mb-1"><strong>Tracking Number:</strong> ${shipment.tracking_number}</p>
                                            <p class="mb-1"><strong>Courier:</strong> ${shipment.courier}</p>
                                            <p class="mb-1"><strong>Status:</strong>
                                                <span class="badge bg-${shipment.status_color}">${shipment.status}</span>
                                            </p>
                                            <p class="mb-1"><strong>Shipped:</strong> ${shipment.shipped_at}</p>
                                            <p class="mb-1"><strong>Estimated Delivery:</strong> ${shipment.estimated_delivery_at || 'N/A'}</p>
                                            ${shipment.actual_delivery_at ?
                                                `<p class="mb-0"><strong>Delivered:</strong> ${shipment.actual_delivery_at}</p>` : ''}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Quick Actions</h6>
                                            <div class="d-grid gap-2">
                                                ${shipment.tracking_url ? `
                                                                                            <a href="${shipment.tracking_url}" target="_blank" class="btn btn-outline-primary">
                                                                                                <i class="mdi mdi-link me-1"></i> Track on Courier Website
                                                                                            </a>
                                                                                        ` : ''}
                                                ${!shipment.is_delivered ? `
                                                                                            <button type="button" class="btn btn-success" id="markAsDeliveredBtn" data-shipment-id="${shipment.id}">
                                                                                                <i class="mdi mdi-check-circle me-1"></i> Mark as Delivered
                                                                                            </button>
                                                                                        ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    `;

                    if (shipment.timeline && shipment.timeline.length > 0) {
                        html += `
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Tracking History</h6>
                                    <div class="tracking-timeline">
                        `;

                        shipment.timeline.forEach((record, index) => {
                            html += `
                                <div class="tracking-timeline-item">
                                    <div class="tracking-timeline-marker ${index === 0 ? 'current' : ''}"></div>
                                    <div class="tracking-timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1">
                                                <span class="badge bg-${record.color}">${record.status}</span>
                                            </h6>
                                            <small class="text-muted">${record.date}</small>
                                        </div>
                                        <p class="mb-0">${record.notes}</p>
                                    </div>
                                </div>
                            `;
                        });

                        html += `
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    html += `</div>`;

                    $('#trackingContent').html(html);

                    // Show/hide mark delivered button
                    $('#markDeliveredBtn').toggle(!shipment.is_delivered);
                }

                // Mark as delivered from tracking modal
                $(document).on('click', '#markAsDeliveredBtn', function() {
                    const shipmentId = $(this).data('shipment-id');

                    Swal.fire({
                        title: 'Mark as Delivered?',
                        text: 'This will mark the shipment as delivered',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Mark Delivered'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Processing...',
                                text: 'Updating shipment status',
                                icon: 'info',
                                showConfirmButton: false,
                                allowOutsideClick: false
                            });

                            $.ajax({
                                url: "{{ route('orders.shipments.mark-delivered', ':shipmentId') }}"
                                    .replace(':shipmentId', shipmentId),
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                        $('#trackingModal').modal('hide');
                                        setTimeout(() => {
                                            location.reload();
                                        }, 2000);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'An error occurred while updating',
                                        'error');
                                }
                            });
                        }
                    });
                });

                $(document).on('click', '.cancel-order-btn', function() {
                    const orderId = $(this).data('id');
                    const orderAmount = $(this).data('amount');
                    const paymentType = $(this).data('payment-type');

                    $('#cancelOrderId').val(orderId);
                    $('#refundAmount').val(orderAmount);
                    $('#refundAmountDisplay').val('₹' + orderAmount);

                    const modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
                    modal.show();
                });

                $('#confirmCancelBtn').click(function() {
                    if (!$('#confirmCancel').is(':checked')) {
                        Swal.fire('Error', 'Please confirm cancellation', 'error');
                        return;
                    }

                    const formData = $('#cancelOrderForm').serialize();

                    $.ajax({
                        url: "{{ route('orders.cancel') }}",
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success', response.message, 'success');
                                $('#cancelOrderModal').modal('hide');
                                location.reload();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'An error occurred while processing your request',
                                'error');
                        }
                    });
                });

                $(document).on('click', '.cancel-product-btn', function() {
                    const orderId = $(this).data('order-id');
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');
                    const maxAmount = $(this).data('max-amount');

                    $('#cancelProductOrderId').val(orderId);
                    $('#cancelProductItemId').val(itemId);
                    $('#maxRefundAmount').text(maxAmount);
                    $('#product_refund_amount').val(maxAmount);
                    $('#product_refund_amount').attr('max', maxAmount);

                    $('#cancelProductModalLabel').html(`Cancel Product: ${productName}`);

                    const modal = new bootstrap.Modal(document.getElementById('cancelProductModal'));
                    modal.show();
                });

                $('#confirmCancelProductBtn').click(function() {
                    if (!$('#confirmProductCancel').is(':checked')) {
                        Swal.fire('Error', 'Please confirm cancellation', 'error');
                        return;
                    }

                    const formData = $('#cancelProductForm').serialize();

                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will cancel the product and initiate refund if applicable',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, cancel it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.cancel-product', ['order' => Crypt::encrypt($order->id), 'orderItem' => ':itemId']) }}"
                                    .replace(':itemId', itemId),
                                type: 'POST',
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('Success', response.message, 'success');
                                        $('#cancelProductModal').modal('hide');

                                        const row = $(`#item-row-${response.item_id}`);
                                        row.addClass('table-danger');
                                        row.find('.cancel-product-btn').remove();

                                        setTimeout(() => {
                                            location.reload();
                                        }, 2000);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error',
                                        'An error occurred while processing your request',
                                        'error');
                                }
                            });
                        }
                    });
                });

                $(document).on('click', '.view-history-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');

                    $('#productHistoryModalLabel').html(`Product History: ${productName}`);

                    $.ajax({
                        url: "{{ route('orders.product-history', ':itemId') }}".replace(':itemId',
                            itemId),
                        type: 'GET',
                        beforeSend: function() {
                            $('#productHistoryContent').html(`
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading history...</p>
                                </div>
                            `);
                        },
                        success: function(response) {
                            if (response.success) {
                                if (response.data.length > 0) {
                                    let html = `
                                        <div class="tracking-timeline">
                                    `;

                                    response.data.forEach((record, index) => {
                                        const statusColors = {
                                            'cancelled': 'danger',
                                            'delivered': 'success',
                                            'shipped': 'primary',
                                            'processing': 'info',
                                            'pending': 'warning',
                                            'return_requested': 'warning',
                                            'replace_requested': 'info',
                                            'returned': 'secondary',
                                            'refunded': 'info'
                                        };

                                        html += `
                                            <div class="tracking-timeline-item ${index === 0 ? 'current' : ''}">
                                                <div class="tracking-timeline-marker"></div>
                                                <div class="tracking-timeline-content">
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="mb-1">
                                                            <span class="badge bg-${statusColors[record.status.toLowerCase()] || 'secondary'}">
                                                                ${record.status}
                                                            </span>
                                                        </h6>
                                                        <small class="text-muted">${record.created_at}</small>
                                                    </div>
                                                    <p class="mb-0">${record.notes || 'No notes'}</p>
                                                    <small class="text-muted">By: ${record.created_by}</small>
                                                </div>
                                            </div>
                                        `;
                                    });

                                    html += `</div>`;
                                    $('#productHistoryContent').html(html);
                                } else {
                                    $('#productHistoryContent').html(`
                                        <div class="alert alert-info">
                                            <i class="mdi mdi-information-outline me-2"></i>
                                            No history available for this product.
                                        </div>
                                    `);
                                }
                            } else {
                                $('#productHistoryContent').html(`
                                    <div class="alert alert-danger">
                                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                                        Failed to load history.
                                    </div>
                                `);
                            }
                        },
                        error: function() {
                            $('#productHistoryContent').html(`
                                <div class="alert alert-danger">
                                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                                    Error loading history.
                                </div>
                            `);
                        }
                    });

                    const modal = new bootstrap.Modal(document.getElementById('productHistoryModal'));
                    modal.show();
                });

                $(document).on('click', '.process-refund-btn', function() {
                    const itemId = $(this).data('item-id');
                    const productName = $(this).data('product');
                    const maxAmount = $(this).data('max-amount');

                    $('#refundItemId').val(itemId);
                    $('#refund_product_name').val(productName);
                    $('#partial_refund_amount').val(maxAmount);
                    $('#partial_refund_amount').attr('max', maxAmount);

                    $('#partialRefundModalLabel').html(`Process Refund: ${productName}`);

                    const modal = new bootstrap.Modal(document.getElementById('partialRefundModal'));
                    modal.show();
                });

                $('#confirmPartialRefundBtn').click(function() {
                    if (!$('#confirmPartialRefund').is(':checked')) {
                        Swal.fire('Error', 'Please confirm refund', 'error');
                        return;
                    }

                    const formData = $('#partialRefundForm').serialize();

                    Swal.fire({
                        title: 'Process Refund?',
                        text: 'This will initiate a refund to the customer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Process Refund'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('orders.partial-refund', ['order' => Crypt::encrypt($order->id)]) }}",
                                type: 'POST',
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                        $('#partialRefundModal').modal('hide');

                                        setTimeout(() => {
                                            location.reload();
                                        }, 2000);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error',
                                        'An error occurred while processing refund',
                                        'error');
                                }
                            });
                        }
                    });
                });

                $('#exportOrderBtn').click(function(e) {
                    e.preventDefault();
                    window.location.href =
                        "{{ url('admin/orders/export-single/' . Crypt::encrypt($order->id)) }}";
                });

                @if (session('status'))
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    Toast.fire({
                        icon: '{{ session('status')['type'] }}',
                        title: '{{ session('status')['message'] }}'
                    });
                @endif
            });
        </script>
    @endpush
@endsection
