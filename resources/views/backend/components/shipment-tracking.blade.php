<!-- resources/views/components/shipment-tracking.blade.php -->
<div class="card shipment-tracking-card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="mdi mdi-truck-delivery me-2"></i>
            Shipment Tracking
        </h5>
    </div>
    <div class="card-body">
        @if($shipment)
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="shipment-info">
                        <p class="mb-1">
                            <strong>Tracking Number:</strong>
                            <span class="text-primary">{{ $shipment->tracking_number }}</span>
                        </p>
                        <p class="mb-1">
                            <strong>Courier:</strong>
                            {{ $shipment->courier->name }}
                        </p>
                        <p class="mb-1">
                            <strong>Shipped On:</strong>
                            {{ $shipment->shipped_at->format('d M Y') }}
                        </p>
                        <p class="mb-1">
                            <strong>Estimated Delivery:</strong>
                            <span class="{{ $shipment->is_overdue ? 'text-danger' : 'text-success' }}">
                                {{ $shipment->estimated_delivery_at?->format('d M Y') ?? 'N/A' }}
                            </span>
                            @if($shipment->is_overdue)
                                <span class="badge bg-danger ms-2">Overdue</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="current-status">
                        <p class="mb-2">
                            <strong>Current Status:</strong>
                        </p>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-{{ $shipment->status_color }} p-2 me-2">
                                {{ $shipment->status_description }}
                            </span>
                            @if($shipment->actual_delivery_at)
                                <small class="text-muted">
                                    Delivered on {{ $shipment->actual_delivery_at->format('d M Y h:i A') }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking Timeline -->
            <div class="tracking-timeline">
                @php
                    $statuses = [
                        'picked_up' => ['icon' => 'mdi-package-variant', 'label' => 'Picked Up'],
                        'in_transit' => ['icon' => 'mdi-truck', 'label' => 'In Transit'],
                        'out_for_delivery' => ['icon' => 'mdi-motorbike', 'label' => 'Out for Delivery'],
                        'delivered' => ['icon' => 'mdi-check-circle', 'label' => 'Delivered'],
                    ];

                    $currentStatusIndex = array_search($shipment->status->value, array_keys($statuses));
                @endphp

                <div class="timeline">
                    @foreach($statuses as $key => $status)
                        <div class="timeline-step {{ $currentStatusIndex >= array_search($key, array_keys($statuses)) ? 'completed' : '' }}">
                            <div class="timeline-icon">
                                <i class="mdi {{ $status['icon'] }}"></i>
                            </div>
                            <div class="timeline-label">
                                <span>{{ $status['label'] }}</span>
                                @if($key === 'picked_up')
                                    <small class="d-block">{{ $shipment->shipped_at->format('d M') }}</small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tracking History -->
            @if($shipment->statusHistory->count() > 0)
                <div class="mt-4">
                    <h6 class="mb-3">Tracking History</h6>
                    <div class="list-group">
                        @foreach($shipment->statusHistory->sortByDesc('created_at') as $history)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-{{ $history->status_color }} me-2">
                                            {{ $history->status_description }}
                                        </span>
                                        <span>{{ $history->notes }}</span>
                                    </div>
                                    <small class="text-muted">
                                        {{ $history->created_at->format('d M Y h:i A') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="mt-4">
                @if($shipment->tracking_url && $shipment->tracking_url !== '#')
                    <a href="{{ $shipment->tracking_url }}" target="_blank"
                       class="btn btn-outline-primary btn-sm me-2">
                        <i class="mdi mdi-link me-1"></i> Track on Courier Website
                    </a>
                @endif

                @if(!$shipment->is_delivered)
                    <button type="button" class="btn btn-success btn-sm mark-delivered-btn"
                            data-shipment-id="{{ $shipment->id }}">
                        <i class="mdi mdi-check-circle me-1"></i> Mark as Delivered
                    </button>
                @endif
            </div>
        @else
            <div class="alert alert-info">
                <i class="mdi mdi-information-outline me-2"></i>
                No shipment information available.
            </div>
        @endif
    </div>
</div>

<style>
.shipment-tracking-card .tracking-timeline {
    position: relative;
    padding: 20px 0;
}

.shipment-tracking-card .timeline {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.shipment-tracking-card .timeline:before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #dee2e6;
    z-index: 1;
}

.shipment-tracking-card .timeline-step {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
}

.shipment-tracking-card .timeline-step.completed .timeline-icon {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.shipment-tracking-card .timeline-step.completed:before {
    background: #0d6efd;
}

.shipment-tracking-card .timeline-icon {
    width: 40px;
    height: 40px;
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    color: #6c757d;
}

.shipment-tracking-card .timeline-label {
    font-size: 12px;
    color: #6c757d;
}
</style>
