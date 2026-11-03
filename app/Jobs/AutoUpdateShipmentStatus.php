<?php

namespace App\Jobs;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoUpdateShipmentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $shipmentId;

    public function __construct($shipmentId)
    {
        $this->shipmentId = $shipmentId;
    }

    public function handle(): void
    {
        try {
            $shipment = Shipment::with(['courier', 'orderItem'])->find($this->shipmentId);

            if (!$shipment) {
                Log::warning("Shipment {$this->shipmentId} not found for auto-update");
                return;
            }

            if (!$shipment->courier || !$shipment->courier->shouldAutoUpdateDelivery()) {
                return;
            }

            // Simulate status update based on time
            $updated = $shipment->simulateStatusUpdate();

            if ($updated) {
                Log::info("Auto-updated shipment {$shipment->id} to status: {$shipment->status->value}");

                // Reschedule if not in final status
                if (!$shipment->is_delivered &&
                    $shipment->status->value !== \App\Enums\DeliveryStatus::RETURNED->value &&
                    $shipment->status->value !== \App\Enums\DeliveryStatus::CANCELLED->value) {

                    $shipment->scheduleAutoUpdate();
                }
            } else {
                // Reschedule for next check
                $shipment->scheduleAutoUpdate();
            }
        } catch (\Exception $e) {
            Log::error("Failed to auto-update shipment {$this->shipmentId}: " . $e->getMessage());
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AutoUpdateShipmentStatus job failed for shipment {$this->shipmentId}: " . $exception->getMessage());
    }
}
