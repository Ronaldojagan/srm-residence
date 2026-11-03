<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Enums\DeliveryStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoUpdateDeliveries extends Command
{
    protected $signature = 'deliveries:auto-update';
    protected $description = 'Automatically update shipment statuses based on elapsed time';

    public function handle(): int
    {
        $this->info('Starting automatic delivery updates...');

        // Get shipments that need auto-update
        $shipments = Shipment::with(['courier', 'orderItem'])
            ->needsAutoUpdate()
            ->get();

        $this->info("Found {$shipments->count()} shipments to update");

        $updated = 0;
        $skipped = 0;

        foreach ($shipments as $shipment) {
            try {
                $oldStatus = $shipment->status->value;
                $result = $shipment->simulateStatusUpdate();

                if ($result) {
                    $newStatus = $shipment->status->value;
                    $this->line("✓ Updated shipment {$shipment->id}: {$oldStatus} → {$newStatus}");
                    $updated++;

                    // Reschedule if not in final status
                    if (!in_array($newStatus, [
                        DeliveryStatus::DELIVERED->value,
                        DeliveryStatus::RETURNED->value,
                        DeliveryStatus::CANCELLED->value
                    ])) {
                        $shipment->scheduleAutoUpdate();
                    }
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to update shipment {$shipment->id}: " . $e->getMessage());
                $this->error("✗ Failed to update shipment {$shipment->id}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("✅ Update complete!");
        $this->info("Updated: {$updated}, Skipped: {$skipped}");

        // Schedule next run
        $this->call('schedule:run');

        return Command::SUCCESS;
    }
}
