<div class="order-details">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Order #{{ $order->order_no ?? 'N/A' }}</h5>
        <div>
            @php
                $statusLabels = [
                    \App\Enums\OrderStatus::PENDING => 'warning',
                    \App\Enums\OrderStatus::PROCESSING => 'primary',
                    \App\Enums\OrderStatus::SHIPPED => 'info',
                    \App\Enums\OrderStatus::COMPLETED => 'success',
                    \App\Enums\OrderStatus::DELIVERED => 'success',
                    \App\Enums\OrderStatus::CANCELLED => 'danger',
                    \App\Enums\OrderStatus::REFUNDED => 'secondary',
                ];

                // Get the order status (convert to string if needed)
                $orderStatus = $order->status ?? 'pending';
                $badgeClass = $statusLabels[$orderStatus] ?? 'dark';
            @endphp

            <span class="badge bg-{{ $badgeClass }}">
                {{ \App\Enums\OrderStatus::getDescription($orderStatus) ?? ucfirst($orderStatus) }}
            </span>

            @if(isset($shopOrder) && $shopOrder)
                <span class="badge bg-info ms-2">
                    Shop Order #{{ $shopOrder->shop_order_no ?? 'N/A' }}
                </span>
            @endif
        </div>
    </div>

    @if(isset($shopOrder) && $shopOrder)
    <div class="card bg-light mb-4">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted d-block">Shop Order Total</small>
                    <strong>{{ $currency }}{{ number_format($shopOrder->total_amount ?? 0, 2) }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Shop Commission</small>
                    <strong>{{ $currency }}{{ number_format($shopOrder->total_shop_commission ?? 0, 2) }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Admin Commission</small>
                    <strong>{{ $currency }}{{ number_format($shopOrder->total_admin_commission ?? 0, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-6">
            <h6 class="text-muted">Order Information</h6>
            <p class="mb-1"><strong>Date:</strong> {{ $order->created_at->format($date_format ?? 'd M Y') ?? 'N/A' }}</p>
            <p class="mb-1"><strong>Total:</strong> {{ $currency }}{{ number_format($order->total_amount ?? 0, 2) }}</p>

            @if(isset($order->payment) && $order->payment)
                <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst($order->payment->payment_method ?? 'N/A') }}</p>
                <p class="mb-1"><strong>Payment Status:</strong>
                    @php
                        $paymentStatus = $order->payment->status ?? null;
                        $paymentBadgeClass = 'secondary';

                        if ($paymentStatus === 'completed') {
                            $paymentBadgeClass = 'success';
                        } elseif ($paymentStatus === 'pending') {
                            $paymentBadgeClass = 'warning';
                        } elseif (in_array($paymentStatus, ['failed', 'cancelled'])) {
                            $paymentBadgeClass = 'danger';
                        }
                    @endphp
                    <span class="badge bg-{{ $paymentBadgeClass }}">
                        {{ ucfirst($paymentStatus ?? 'N/A') }}
                    </span>
                </p>
            @endif

            @if (isset($order->is_replacement) && $order->is_replacement)
                <p class="mb-1"><strong>Replacement Order:</strong> Yes</p>
                @if(isset($order->replacement_notes) && $order->replacement_notes)
                    <p class="mb-1"><strong>Replacement Notes:</strong> {{ $order->replacement_notes }}</p>
                @endif
            @endif
        </div>

        <div class="col-md-6">
            <h6 class="text-muted">Customer Information</h6>
            <p class="mb-1"><strong>Name:</strong> {{ $order->user->name ?? 'Guest' }}</p>
            <p class="mb-1"><strong>Phone:</strong> {{ $order->user->phone ?? 'N/A' }}</p>
            @if(isset($order->shippingAddress) && $order->shippingAddress)
                <p class="mb-1"><strong>Address:</strong> {{ $order->shippingAddress->full_address ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Pincode:</strong> {{ $order->shippingAddress->pincode ?? 'N/A' }}</p>
            @endif
        </div>
    </div>

    @php
        // Use orderItems from the order OR passed separately
        $displayItems = isset($orderItems) ? $orderItems : ($order->orderItems ?? collect());
    @endphp

    <h6 class="text-muted mb-3">
        @if(isset($shopOrder) && $shopOrder)
            Shop Items ({{ $displayItems->count() }} items)
        @else
            All Order Items ({{ $displayItems->count() }} items)
        @endif
    </h6>

    @if($displayItems->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th width="50">#</th>
                    <th>Product</th>
                    <th>Shop</th>
                    <th width="100" class="text-end">Price</th>
                    <th width="80" class="text-center">Qty</th>
                    <th width="120" class="text-end">Total</th>
                    <th width="120" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($displayItems as $index => $item)
                    @php
                        // Handle OrderItemStatus enum
                        if ($item->status instanceof \App\Enums\OrderItemStatus) {
                            $itemStatus = $item->status->value;
                        } else {
                            $itemStatus = $item->status ?? 'pending';
                        }

                        $itemStatusBadges = [
                            'pending' => 'warning',
                            'confirmed' => 'info',
                            'processing' => 'primary',
                            'shipped' => 'info',
                            'delivered' => 'success',
                            'cancelled' => 'danger',
                            'return_requested' => 'warning',
                            'returned' => 'secondary',
                            'replaced' => 'dark',
                            'refunded' => 'secondary',
                        ];

                        $itemBadgeClass = $itemStatusBadges[$itemStatus] ?? 'dark';
                        $itemStatusDescription = \App\Enums\OrderItemStatus::getDescription($itemStatus) ?? ucfirst($itemStatus);
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if (isset($item->product) && $item->product)
                                    @php
                                        $imageUrl = $item->product->image ?
                                            url('storage/product/' . $item->product->image) :
                                            asset('assets/images/default-product.png');
                                    @endphp
                                    <img src="{{ $imageUrl }}"
                                        class="rounded me-2" width="40" height="40"
                                        alt="{{ $item->product->name ?? 'Product' }}"
                                        style="object-fit: cover;"
                                        onerror="this.src='{{ asset('assets/images/default-product.png') }}'">
                                @else
                                    <img src="{{ asset('assets/images/default-product.png') }}"
                                        class="rounded me-2" width="40" height="40"
                                        alt="Product"
                                        style="object-fit: cover;">
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $item->product_name ?? ($item->product->name ?? 'Product') }}</h6>
                                    @if (isset($item->quantity_unit) && $item->quantity_unit)
                                        <small class="text-muted">{{ $item->quantity_unit }}</small>
                                    @endif
                                    @if(isset($item->description) && $item->description)
                                        <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($item->description, 50) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if(isset($item->shop) && $item->shop)
                                {{ $item->shop->name }}
                            @else
                                <span class="text-muted">
                                    @if(isset($item->shop_id) && $item->shop_id)
                                        Shop ID: {{ $item->shop_id }}
                                    @else
                                        Shop not found
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td class="text-end">{{ $currency }}{{ number_format($item->amount ?? 0, 2) }}</td>
                        <td class="text-center">{{ $item->quantity ?? 1 }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($item->total_amount ?? 0, 2) }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $itemBadgeClass }}" style="font-size: 0.8em;">
                                {{ $itemStatusDescription }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @if(isset($shopOrder) && $shopOrder)
                    <tr class="table-info">
                        <td colspan="4" class="text-end"><strong>Amount:</strong></td>
                        <td colspan="3" class="text-end">
                            <strong>{{ $currency }}{{ number_format($shopOrder->total_shop_commission ?? 0, 2) }}</strong>
                        </td>
                    </tr>
                    <tr class="table-info">
                        <td colspan="4" class="text-end"><strong>Admin Commission ({{ $shopOrder->commission_percentage ?? 0 }}%):</strong></td>
                        <td colspan="3" class="text-end">
                            <strong>{{ $currency }}{{ number_format($shopOrder->total_admin_commission ?? 0, 2) }}</strong>
                        </td>
                    </tr>
                    <tr class="table-info">
                        <td colspan="4" class="text-end"><strong>Total Amount:</strong></td>
                        <td colspan="3" class="text-end">
                            <strong>{{ $currency }}{{ number_format($shopOrder->total_amount ?? 0, 2) }}</strong>
                        </td>
                    </tr>
                @elseif(isset($order->precheckout) && $order->precheckout)
                    @php
                        $precheckout = $order->precheckout;
                    @endphp
                    <tr>
                        <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                        <td class="text-end">{{ $currency }}{{ number_format($precheckout->sub_total ?? 0, 2) }}</td>
                    </tr>
                    @if (($precheckout->coupon_amount ?? 0) > 0)
                        <tr>
                            <td colspan="6" class="text-end"><strong>Coupon Discount:</strong></td>
                            <td class="text-end">
                                -{{ $currency }}{{ number_format($precheckout->coupon_amount, 2) }}
                            </td>
                        </tr>
                    @endif
                    @if (($precheckout->platform_fee ?? 0) > 0)
                        <tr>
                            <td colspan="6" class="text-end"><strong>Platform Fees:</strong></td>
                            <td class="text-end">
                                {{ $currency }}{{ number_format($precheckout->platform_fee, 2) }}
                            </td>
                        </tr>
                    @endif
                    @if (($precheckout->delivery_charge ?? 0) > 0)
                        <tr>
                            <td colspan="6" class="text-end"><strong>Delivery Charge:</strong></td>
                            <td class="text-end">
                                {{ $currency }}{{ number_format($precheckout->delivery_charge, 2) }}
                            </td>
                        </tr>
                    @endif
                @endif

                @if(!isset($shopOrder))
                    <tr class="fw-bold table-success">
                        <td colspan="6" class="text-end"><strong>Grand Total:</strong></td>
                        <td class="text-end">{{ $currency }}{{ number_format($order->total_amount ?? 0, 2) }}</td>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>
    @else
    <div class="alert alert-info">
        <i class="mdi mdi-information-outline me-2"></i>
        No order items found @if(isset($shopOrder) && $shopOrder) for this shop @endif.
    </div>
    @endif

    <!-- Delivery Information -->
    @if(isset($order->has_shipments) && ($order->has_shipments || $order->all_items_delivered))
    <div class="card bg-light mt-3">
        <div class="card-body">
            <h6 class="text-muted">Delivery Information</h6>
            <div class="row">
                @if($order->has_shipments)
                <div class="col-md-4">
                    <p class="mb-1"><strong>Shipments:</strong> {{ $order->shipments_count ?? 0 }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Delivered:</strong> {{ $order->delivered_shipments_count ?? 0 }}</p>
                </div>
                @endif
                @if($order->all_items_delivered && $order->delivery_completed_at)
                <div class="col-md-4">
                    <p class="mb-1"><strong>Delivery Completed:</strong>
                        {{ $order->delivery_completed_at->format($date_format ?? 'd M Y H:i') }}
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if (isset($order->notes) && $order->notes)
        <div class="card bg-light mt-3">
            <div class="card-body">
                <h6 class="text-muted">Order Notes</h6>
                <p class="mb-0">{{ $order->notes }}</p>
            </div>
        </div>
    @endif

    @if (isset($order->status) && $order->status == \App\Enums\OrderStatus::CANCELLED && isset($order->cancellation_reason) && $order->cancellation_reason)
        <div class="card border-danger mt-3">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0 text-white">Cancellation Details</h6>
            </div>
            <div class="card-body">
                @if(isset($order->cancelled_at) && $order->cancelled_at)
                <p class="mb-1"><strong>Cancellation Date:</strong>
                    {{ $order->cancelled_at->format($date_format ?? 'd M Y H:i') }}
                </p>
                @endif
                <p class="mb-0"><strong>Reason for Cancellation:</strong></p>
                <p class="mb-0 text-danger">{{ $order->cancellation_reason }}</p>
            </div>
        </div>
    @endif

    <!-- Replacement Order Details -->
    @if(isset($order->is_replacement) && $order->is_replacement && method_exists($order, 'getReplacementDetails'))
        @php
            $replacementDetails = $order->getReplacementDetails();
        @endphp
        @if(is_array($replacementDetails) && !empty($replacementDetails))
        <div class="card border-info mt-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0 text-white">Replacement Details</h6>
            </div>
            <div class="card-body">
                @if(isset($replacementDetails['original_order']) && is_array($replacementDetails['original_order']))
                    <p class="mb-1">
                        <strong>Original Order:</strong>
                        #{{ $replacementDetails['original_order']['order_no'] ?? 'N/A' }}
                        @if(isset($replacementDetails['original_order']['date']))
                            ({{ $replacementDetails['original_order']['date'] }})
                        @endif
                    </p>
                @endif
                @if(isset($replacementDetails['replaced_item']) && is_array($replacementDetails['replaced_item']))
                    <p class="mb-1">
                        <strong>Replaced Item:</strong> {{ $replacementDetails['replaced_item']['product_name'] ?? 'N/A' }}
                    </p>
                    @if(isset($replacementDetails['replaced_item']['reason']) && $replacementDetails['replaced_item']['reason'])
                        <p class="mb-0">
                            <strong>Reason:</strong> {{ $replacementDetails['replaced_item']['reason'] }}
                        </p>
                    @endif
                @endif
                @if(isset($replacementDetails['replacement_notes']) && $replacementDetails['replacement_notes'])
                    <p class="mb-0 mt-2">
                        <strong>Notes:</strong> {{ $replacementDetails['replacement_notes'] }}
                    </p>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>
