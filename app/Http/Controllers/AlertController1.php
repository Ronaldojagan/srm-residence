// Add these methods to your OrderService class

public function createShipment(Order $order, array $data)
{
    DB::beginTransaction();

    try {
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'order_item_id' => $data['order_item_id'] ?? null,
            'courier_id' => $data['courier_id'],
            'tracking_number' => $data['tracking_number'],
            'estimated_delivery_at' => $data['estimated_delivery_at'] ?? now()->addDays($data['estimated_days'] ?? 3),
            'recipient_name' => $order->shippingAddress->name ?? $order->user->name,
            'recipient_phone' => $order->shippingAddress->phone ?? $order->user->phone,
            'delivery_address' => $order->shippingAddress->full_address ?? '',
            'notes' => $data['notes'] ?? null
        ]);

        // Update order item status to shipped
        if ($shipment->orderItem) {
            $shipment->orderItem->updateStatus(
                \App\Enums\OrderItemStatus::SHIPPED->value,
                'Shipped via ' . $shipment->courier->name
            );
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($order)
            ->withProperties($shipment->toArray())
            ->log('Shipment created');

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Shipment created successfully',
            'shipment' => $shipment->load('courier')
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to create shipment: ' . $e->getMessage()
        ], 500);
    }
}

public function markShipmentDelivered(Shipment $shipment, array $data)
{
    DB::beginTransaction();

    try {
        $shipment->markAsDelivered(
            $data['delivery_proof_url'] ?? null,
            $data['notes'] ?? null
        );

        activity()
            ->causedBy(auth()->user())
            ->performedOn($shipment->order)
            ->withProperties($shipment->toArray())
            ->log('Shipment marked as delivered');

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Shipment marked as delivered',
            'shipment' => $shipment->fresh()
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to mark shipment as delivered: ' . $e->getMessage()
        ], 500);
    }
}

public function getShipmentTracking(Shipment $shipment)
{
    try {
        $timeline = $shipment->statusHistory->map(function ($history) {
            return [
                'status' => $history->status_description,
                'color' => $history->status_color,
                'notes' => $history->notes,
                'date' => $history->created_at->format('d M Y h:i A'),
                'metadata' => $history->formatted_metadata
            ];
        });

        return response()->json([
            'success' => true,
            'shipment' => [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status_description,
                'status_color' => $shipment->status_color,
                'courier' => $shipment->courier->name,
                'tracking_url' => $shipment->tracking_url,
                'shipped_at' => $shipment->shipped_at->format('d M Y'),
                'estimated_delivery_at' => $shipment->estimated_delivery_at?->format('d M Y'),
                'actual_delivery_at' => $shipment->actual_delivery_at?->format('d M Y h:i A'),
                'is_delivered' => $shipment->is_delivered,
                'is_overdue' => $shipment->is_overdue,
                'days_overdue' => $shipment->days_overdue,
                'timeline' => $timeline
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch tracking information'
        ], 500);
    }
}


# Create migrations
php artisan make:migration create_couriers_table
php artisan make:migration create_shipments_table
php artisan make:migration create_shipment_status_histories_table

# Run migrations
php artisan migrate

# Seed couriers
php artisan db:seed --class=CourierSeeder

# Create command
php artisan make:command AutoUpdateDeliveries

# Create job
php artisan make:job AutoUpdateShipmentStatus
