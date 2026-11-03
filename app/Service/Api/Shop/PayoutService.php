<?php

namespace App\Service\Api\Shop;

use App\Enums\OrderItemStatus;
use App\Models\OrderItem;
use App\Models\PayoutShop;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PayoutService
{
    public function payout($request)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found'
            ], 404);
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth();

        $currentMonthPayouts = OrderItem::where('shop_id', $shop->id)
            ->where('status', OrderItemStatus::DELIVERED->value)
            ->whereNotNull('delivered_at')
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->sum('total_amount');

        $allPayouts = PayoutShop::where('shop_id', $shop->id)
            ->with('payout')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalAmount = $allPayouts->sum('amount');
        $totalCount = $allPayouts->count();
        $totalPayable = $allPayouts->where('status', 'paid')->sum('payable_amount');
        $totalPendingPayable = $allPayouts->where('status', 'pending')->sum('payable_amount');

        return response()->json([
            'success' => 1,
            'data' => [
                'overall' => [
                    'total_amount' => (int) $totalAmount,
                    'total_count' => (int) $totalCount,
                    'pending_amount' => (int) $totalPendingPayable,
                    'paid_amount' => (int) $totalPayable,
                    'current_payout_amount' => (int)$currentMonthPayouts,
                ],
                'payout_history' => $allPayouts->map(function ($payout) {
                    return [
                        'payout_id' => $payout->id,
                        'date_range' => Carbon::parse($payout->payout->start_date)->format('d-m-Y') . ' - ' .
                            Carbon::parse($payout->payout->end_date)->format('d-m-Y'),
                        'amount' => (float) $payout->amount,
                        'commission' => (float) $payout->commission,
                        'commission_percentage' => (float) $payout->commission_percentage,
                        'payable_amount' => (float) $payout->payable_amount,
                        'status' => ucfirst($payout->status),
                        'created_at' => Carbon::parse($payout->created_at)->format('d-m-Y h:i A'),
                    ];
                }),
            ]
        ]);
    }

    public function payoutDetails($id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found'
            ], 404);
        }

        $payout = PayoutShop::where('id', $id)
            ->where('shop_id', $shop->id)
            ->with(['payout', 'orderItems'])
            ->first();

        if (!$payout) {
            return response()->json([
                'success' => false,
                'message' => 'Payout not found'
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'payout_info' => [
                    'payout_id' => $payout->payout->id,
                    'payout_shop_id' => $payout->id,
                    'date_range' => Carbon::parse($payout->payout->start_date)->format('d-m-Y') . ' - ' .
                        Carbon::parse($payout->payout->end_date)->format('d-m-Y'),
                    'amount' => (float) $payout->amount,
                    'commission' => (float) $payout->commission,
                    'commission_percentage' => (float) $payout->commission_percentage,
                    'payable_amount' => (float) $payout->payable_amount,
                    'status' => $payout->status,
                    'status_label' => ucfirst($payout->status),
                ],
            ]
        ]);
    }
}
