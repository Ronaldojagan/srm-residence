<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatuses extends Command
{
    protected $signature = 'orders:update-statuses
                            {--days=30 : Number of days to look back for orders}
                            {--order-id= : Specific order ID to update}
                            {--dry-run : Show what would be updated without making changes}';

    protected $description = 'Update main order statuses based on order item statuses';

    public function handle()
    {
        $orderId = $this->option('order-id');
        $days = $this->option('days');
        $dryRun = $this->option('dry-run');

        $query = Order::query();

        if ($orderId) {
            $query->where('id', $orderId);
            $this->info("Processing specific order ID: {$orderId}");
        } else {
            $query->where('created_at', '>=', now()->subDays($days));
            $this->info("Processing orders from last {$days} days");
        }

        $orders = $query->with(['orderItems' => function($q) {
            $q->with(['statusHistories' => function($q) {
                $q->recentFirst()->limit(10);
            }]);
        }])->get();

        $this->info("Found {$orders->count()} orders to process");

        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($orders as $order) {
            $oldStatus = $order->status;
            $newStatus = $this->calculateOrderStatus($order);

            if ($oldStatus !== $newStatus) {
                if (!$dryRun) {
                    $order->update([
                        'status' => $newStatus,
                        'status_updated_at' => now(),
                        'status_summary' => $this->generateStatusSummary($order)
                    ]);

                    Log::info('Order status updated', [
                        'order_id' => $order->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'updated_at' => now()
                    ]);
                }

                $this->line("Order #{$order->order_no} (ID: {$order->id}): {$oldStatus} → {$newStatus}");
                $updatedCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn("DRY RUN - No changes were made");
            $this->info("Would update: {$updatedCount} orders");
            $this->info("Would skip: {$skippedCount} orders");
        } else {
            $this->info("Successfully updated: {$updatedCount} orders");
            $this->info("Skipped (no change): {$skippedCount} orders");
        }

        return Command::SUCCESS;
    }

    private function calculateOrderStatus(Order $order): string
    {
        $items = $order->orderItems;

        if ($items->isEmpty()) {
            return OrderStatus::PENDING;
        }

        $statusCounts = $items->groupBy('status')->map->count();
        $totalItems = $items->count();

        if ($this->allItemsDelivered($statusCounts, $totalItems)) {
            return OrderStatus::COMPLETED;
        }

        if ($this->allItemsCancelled($statusCounts, $totalItems)) {
            return OrderStatus::CANCELLED;
        }

        if ($this->hasCancelledItems($statusCounts) && !$this->allItemsCancelled($statusCounts, $totalItems)) {
            return 'Partially Cancelled';
        }

        if ($this->hasShippedItems($statusCounts)) {
            return OrderStatus::SHIPPED;
        }

        if ($this->hasProcessingItems($statusCounts)) {
            return OrderStatus::PROCESSING;
        }

        if ($this->hasPendingItems($statusCounts)) {
            return OrderStatus::PENDING;
        }

        if ($this->hasReturnedItems($statusCounts)) {
            return 'Partially Returned';
        }

        if ($this->hasRefundedItems($statusCounts)) {
            return OrderStatus::REFUNDED;
        }

        return OrderStatus::PENDING;
    }

    private function allItemsDelivered($statusCounts, $totalItems): bool
    {
        $deliveredCount = 0;

        $deliveredCount += $statusCounts['delivered'] ?? 0;
        $deliveredCount += $statusCounts[OrderStatus::DELIVERED] ?? 0;
        $deliveredCount += $statusCounts[strtolower(OrderStatus::DELIVERED)] ?? 0;

        return $deliveredCount === $totalItems;
    }

    private function allItemsCancelled($statusCounts, $totalItems): bool
    {
        $cancelledCount = 0;

        $cancelledCount += $statusCounts['cancelled'] ?? 0;
        $cancelledCount += $statusCounts[OrderStatus::CANCELLED] ?? 0;
        $cancelledCount += $statusCounts[strtolower(OrderStatus::CANCELLED)] ?? 0;

        return $cancelledCount === $totalItems;
    }

    private function hasCancelledItems($statusCounts): bool
    {
        return ($statusCounts['cancelled'] ?? 0) > 0 ||
               ($statusCounts[OrderStatus::CANCELLED] ?? 0) > 0 ||
               ($statusCounts[strtolower(OrderStatus::CANCELLED)] ?? 0) > 0;
    }

    private function hasShippedItems($statusCounts): bool
    {
        return ($statusCounts['shipped'] ?? 0) > 0 ||
               ($statusCounts[OrderStatus::SHIPPED] ?? 0) > 0 ||
               ($statusCounts[strtolower(OrderStatus::SHIPPED)] ?? 0) > 0;
    }

    private function hasProcessingItems($statusCounts): bool
    {
        return ($statusCounts['processing'] ?? 0) > 0 ||
               ($statusCounts[OrderStatus::PROCESSING] ?? 0) > 0 ||
               ($statusCounts[strtolower(OrderStatus::PROCESSING)] ?? 0) > 0;
    }

    private function hasPendingItems($statusCounts): bool
    {
        return ($statusCounts['pending'] ?? 0) > 0 ||
               ($statusCounts[OrderStatus::PENDING] ?? 0) > 0 ||
               ($statusCounts[strtolower(OrderStatus::PENDING)] ?? 0) > 0;
    }

    private function hasReturnedItems($statusCounts): bool
    {
        return ($statusCounts['returned'] ?? 0) > 0 ||
               ($statusCounts['return_requested'] ?? 0) > 0 ||
               ($statusCounts['Partially Returned'] ?? 0) > 0;
    }

    private function hasRefundedItems($statusCounts): bool
    {
        return ($statusCounts['refunded'] ?? 0) > 0 ||
               ($statusCounts[OrderStatus::REFUNDED] ?? 0) > 0 ||
               ($statusCounts[strtolower(OrderStatus::REFUNDED)] ?? 0) > 0;
    }

    private function generateStatusSummary(Order $order): array
    {
        $items = $order->orderItems;

        return [
            'total_items' => $items->count(),
            'status_counts' => $items->groupBy('status')->map->count()->toArray(),
            'last_calculated_at' => now()->toISOString(),
            'progress_percentage' => $this->calculateProgressPercentage($items),
            'next_expected_status' => $this->predictNextStatus($items),
        ];
    }

    private function calculateProgressPercentage($items): int
    {
        $total = $items->count();
        if ($total === 0) return 0;

        $progress = 0;
        foreach ($items as $item) {
            $itemStatus = strtolower($item->status);

            $progress += match($itemStatus) {
                'delivered' => 100,
                'shipped' => 75,
                'processing' => 50,
                'confirmed' => 25,
                'pending' => 10,
                'cancelled', 'refunded', 'returned' => 0,
                default => 0,
            };
        }

        return round($progress / $total);
    }

    private function predictNextStatus($items): string
    {
        $statuses = $items->pluck('status')->unique()->map(function($status) {
            return strtolower($status);
        });

        if ($statuses->contains('shipped')) {
            return 'Delivery expected soon';
        }

        if ($statuses->contains('processing')) {
            return 'Preparing for shipment';
        }

        if ($statuses->contains('confirmed')) {
            return 'Processing will begin soon';
        }

        return 'Order confirmed';
    }
}
