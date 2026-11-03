<?php

namespace App\Http\Controllers;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Payout;
use App\Models\PayoutShop;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PayoutController extends Controller
{
    public function index()
    {
        $existingPayouts = Payout::select('start_date', 'end_date')
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(function ($payout) {
                return [
                    'start' => $payout->start_date->format('Y-m-d'),
                    'end' => $payout->end_date->format('Y-m-d'),
                    'range' => $payout->start_date->format('Y-m-d') . ' to ' . $payout->end_date->format('Y-m-d')
                ];
            })
            ->toArray();

        $lastPayoutEndDate = Payout::orderBy('end_date', 'desc')
            ->first()
            ?->end_date
            ?->addDay()
            ?->format('Y-m-d');

        $stats = [
            'total_payouts' => Payout::count(),
            'total_amount' => Payout::join('payout_shops', 'payouts.id', '=', 'payout_shops.payout_id')
                ->sum('payout_shops.amount'),
            'paid_payouts' => Payout::where('status', 'paid')->count(),
            'pending_payouts' => Payout::where('status', 'generated')->count()
        ];

        return view('backend.payouts.payout_index', compact('existingPayouts', 'lastPayoutEndDate', 'stats'));
    }

    public function datatable(Request $request)
    {
        $payouts = Payout::withCount('payoutShops')
            ->select([
                'payouts.id',
                'start_date',
                'end_date',
                'status',
                'paid_date',
                'created_at'
            ])
            ->addSelect([
                'total_amount' => PayoutShop::selectRaw('SUM(amount)')
                    ->whereColumn('payout_id', 'payouts.id'),
                'total_commission' => PayoutShop::selectRaw('SUM(commission)')
                    ->whereColumn('payout_id', 'payouts.id')
            ]);

        if ($request->has('start_date') && $request->start_date && $request->has('end_date') && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $payouts->where(function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
                });
            });
        }

        return DataTables::of($payouts)
            ->addColumn('date_range', function ($payout) {
                return '<div class="d-flex align-items-center">
                    <div>
                        ' . $payout->start_date->format('d M Y') . '
                        to ' . $payout->end_date->format('d M Y') . '
                    </div>
                </div>';
            })
            ->addColumn('shops_count', function ($payout) {
                return '<span class="badge bg-info">' . $payout->payoutShops->count() . ' Shops</span>';
            })
            ->addColumn('amount', function ($payout) {
                return '<strong class="text-success">₹' . number_format($payout->total_amount, 2) . '</strong>';
            })
            ->addColumn('commission', function ($payout) {
                return '<span class="text-warning">₹' . number_format($payout->total_commission, 2) . '</span>';
            })
            ->addColumn('status_badge', function ($payout) {
                if ($payout->status == 'paid') {
                    return '<span class="payout-index-status-badge badge-paid">
                        <i class="mdi mdi-check-circle"></i> Paid
                    </span>';
                } elseif ($payout->status == 'generated') {
                    return '<span class="payout-index-status-badge badge-generated">
                        <i class="mdi mdi-clock-outline"></i> Pending
                    </span>';
                } elseif ($payout->status == 'processing') {
                    return '<span class="payout-index-status-badge badge-processing">
                        <i class="mdi mdi-clock-outline"></i> Processing
                    </span>';
                } else {
                    return '<span class="payout-index-status-badge badge-failed">
                        <i class="mdi mdi-alert-circle"></i> Failed
                    </span>';
                }
            })
            ->addColumn('created', function ($payout) {
                return '<small class="text-muted">
                    ' . $payout->created_at->format('d M Y') . '<br>
                    ' . $payout->created_at->format('h:i A') . '
                </small>';
            })
            ->addColumn('actions', function ($payout) {
                $viewUrl = url('admin/payouts/' . Crypt::encrypt($payout->id));
                $exportUrl = url('admin/payouts/' . Crypt::encrypt($payout->id) . '/export');

                return '
                    <a href="' . $viewUrl . '" class="btn-icon me-2" title="View">
                        <img src="' . asset('img/eye-icon.png') . '" alt="View" width="30">
                    </a>
                    <a href="' . $exportUrl . '" class="btn-icon me-2" title="Export CSV">
                        <img src="' . asset('img/download-icon.png') . '" alt="Download" width="30">
                    </a>';
            })
            ->rawColumns(['date_range', 'shops_count', 'amount', 'commission', 'status_badge', 'created', 'actions'])
            ->make(true);
    }

    public function getRangeStats(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $payouts = Payout::where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get();

        $payoutsCount = $payouts->count();
        $totalAmount = $payouts->sum('total_amount');
        $totalCommission = $payouts->sum('total_commission');

        $paidPayouts = $payouts->where('status', 'paid')->count();
        $pendingPayouts = $payouts->where('status', 'generated')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'payouts_count' => $payoutsCount,
                'total_amount' => $totalAmount,
                'total_commission' => $totalCommission,
                'paid_payouts' => $paidPayouts,
                'pending_payouts' => $pendingPayouts
            ]
        ]);
    }

    public function exportDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $payouts = Payout::where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->with(['payoutShops.shop'])
            ->get();

        $filename = 'payouts_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        $callback = function () use ($payouts) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Payout ID',
                'Date Range',
                'Shop ID',
                'Shop Name',
                'Amount',
                'Commission',
                'Payable Amount',
                'Commission Rate',
                'Bank Name',
                'Account Number',
                'IFSC Code',
                'Branch',
                'Account Holder Name',
                'Generated On'
            ]);

            foreach ($payouts as $payout) {
                foreach ($payout->payoutShops as $payoutShop) {
                    fputcsv($file, [
                        $payout->id,
                        $payout->start_date->format('Y-m-d') . ' to ' . $payout->end_date->format('Y-m-d'),
                        $payoutShop->shop_id,
                        $payoutShop->shop->name ?? '',
                        $payoutShop->amount,
                        $payoutShop->commission,
                        $payoutShop->payable_amount,
                        ($payoutShop->commission_percentage ?? $payoutShop->shop?->commission_percentage ?? 0) . '%',
                        $payoutShop->bank_name ?? '',
                        $payoutShop->account_number,
                        $payoutShop->ifsc_code ?? '',
                        $payoutShop->branch ?? '',
                        $payoutShop->account_holder_name ?? '',
                        $payout->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|before:today',
            'end_date' => 'required|date|after_or_equal:start_date|before:today',
        ], [
            'start_date.before' => 'Start date must be before today',
            'end_date.before' => 'End date must be before today',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
        ]);
        $startDate = Carbon::createFromFormat('Y-m-d', $validated['start_date'])->startOfDay();
        $endDate   = Carbon::createFromFormat('Y-m-d', $validated['end_date'])->endOfDay();


        $overlappingPayout = Payout::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                    $subQuery->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        })->exists();

        if ($overlappingPayout) {
            return back()->with('error', 'Payout already exists for overlapping date range. Please select different dates.');
        }

        $lastPayout = Payout::orderBy('end_date', 'desc')->first();

        $shops = Shop::with([
            'user.bankDetails' => function ($query) {
                $query->where('status', true)
                    ->latest()
                    ->limit(1);
            }
        ])->get();


        if ($shops->isEmpty()) {
            return back()->with('error', 'No shops found');
        }

        DB::beginTransaction();

        try {
            $payout = Payout::create([
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => Payout::STATUS_GENERATED,
            ]);

            $payoutShopsData = [];
            $shopsWithoutBankDetails = [];

            foreach ($shops as $shop) {
                $bankDetail = $shop->user->bankDetails;

                if (!$bankDetail) {
                    $shopsWithoutBankDetails[] = $shop->name;
                    continue;
                }

                $currentPeriodOrders = OrderItem::where('shop_id', $shop->id)
                    ->where('status', OrderItemStatus::DELIVERED->value)
                    ->whereNotNull('delivered_at')
                    ->whereBetween('delivered_at', [$startDate, $endDate])
                    ->where(function ($q) use ($endDate) {
                        $q->whereNull('refunded_at')
                            ->orWhere('refunded_at', '>', $endDate);
                    })
                    ->get();

                $previousPeriodRefunds = collect();

                if ($lastPayout) {
                    $previousPeriodRefunds = OrderItem::where('shop_id', $shop->id)
                        ->where('status', OrderItemStatus::DELIVERED->value)
                        ->whereNotNull('delivered_at')
                        ->whereNotNull('refunded_at')
                        ->whereBetween('refunded_at', [$startDate, $endDate])
                        ->get();
                }
                $totalAmount = 0;
                $totalCommission = 0;

                foreach ($currentPeriodOrders as $orderItem) {
                    $totalAmount += $orderItem->total_amount;
                    $commission = $orderItem->total_amount * ($shop->commission_percentage / 100);
                    $totalCommission += $commission;
                }

                foreach ($previousPeriodRefunds as $refundItem) {
                    $totalAmount -= $refundItem->refund_amount;
                    // $commission = $refundItem->refund_amount * ($shop->commission_percentage / 100);
                    // $totalCommission -= $commission;
                }

                if ($totalAmount <= 0) {
                    continue;
                }
                $payableAmount = $totalAmount - $totalCommission;

                $payoutShopsData[] = [
                    'payout_id' => $payout->id,
                    'shop_id' => $shop->id,
                    'amount' => $totalAmount,
                    'commission' => $totalCommission,
                    'payable_amount' => $payableAmount,
                    'commission_percentage' => $shop->commission_percentage,

                    'bank_name' => $bankDetail->bank_name,
                    'account_number' => $bankDetail->account_number,
                    'ifsc_code' => $bankDetail->ifsc_code,
                    'branch' => $bankDetail->branch,
                    'account_holder_name' => $bankDetail->holder_name,

                    'orders_count' => $currentPeriodOrders->count(),
                    'refunds_count' => $previousPeriodRefunds->count(),

                    'status' => PayoutShop::STATUS_GENERATED,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (empty($payoutShopsData)) {
                DB::rollBack();

                if (!empty($shopsWithoutBankDetails)) {
                    $shopNames = implode(', ', $shopsWithoutBankDetails);
                    return back()->with('error', 'No shops with active bank details found. The following shops need to add bank details: ' . $shopNames);
                }

                return back()->with('error', 'No payable amount found for any shop in the selected date range');
            }

            PayoutShop::insert($payoutShopsData);

            DB::commit();

            $message = 'Payout generated successfully for ' . count($payoutShopsData) . ' shops';

            if (!empty($shopsWithoutBankDetails)) {
                $shopNames = implode(', ', $shopsWithoutBankDetails);
                $message .= '. The following shops were skipped due to missing bank details: ' . $shopNames;
            }

            return redirect('admin/payouts/' . Crypt::encrypt($payout->id))
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payout generation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to generate payout: ' . $e->getMessage());
        }
    }

    public function calculatePayoutStats()
    {
        $payouts = Payout::with('payoutShops')->get();

        foreach ($payouts as $payout) {
            $totalAmount = $payout->payoutShops->sum('amount');
            $totalCommission = $payout->payoutShops->sum('commission');
            $totalPayable = $payout->payoutShops->sum('payable_amount');

            $payout->update([
                'total_amount' => $totalAmount,
                'total_commission' => $totalCommission,
                'total_payable_amount' => $totalPayable
            ]);
        }

        return response()->json(['message' => 'Payout stats recalculated']);
    }

    public function show(Payout $payout)
    {
        $payout->load(['payoutShops.shop']);

        $stats = [
            'total_shops' => $payout->payoutShops->count(),
            'total_payable' => $payout->payoutShops->sum('payable_amount'),
            'paid_shops' => $payout->payoutShops->where('status', 'paid')->count(),
            'pending_shops' => $payout->payoutShops->where('status', 'generated')->count(),
            'progress_percent' => $payout->payoutShops->count() > 0 ?
                ($payout->payoutShops->where('status', 'paid')->count() / $payout->payoutShops->count()) * 100 : 0
        ];

        return view('backend.payouts.payout_view', compact('payout', 'stats'));
    }

    public function payoutShopsDatatable(Payout $payout)
    {
        $shops = $payout->payoutShops()->with('shop')->select('payout_shops.*');

        return DataTables::of($shops)
            ->addColumn('shop_name', function ($shop) {
                $rate = $shop->amount > 0 ? ($shop->commission / $shop->amount) * 100 : 0;
                return '<div class="d-flex align-items-center">
                <div class="shop-avatar">
                    ' . strtoupper(substr($shop->shop->name ?? 'S', 0, 1)) . '
                </div>
                <div>
                    <h6 class="mb-1 fw-bold">' . ($shop->shop->name ?? 'Unknown Shop') . '</h6>
                    <small class="text-muted">
                        <i class="mdi mdi-percent"></i>
                        Commission Rate: ' . ($rate ?? 0) . '%
                    </small>
                </div>
            </div>';
            })
            ->addColumn('amount', function ($shop) {
                return '<span class="amount-display text-success fw-bold">
                ₹' . number_format($shop->amount, 2) . '
            </span>';
            })
            ->addColumn('commission', function ($shop) {
                return '<span class="amount-display text-warning fw-bold">
                ₹' . number_format($shop->commission, 2) . '
            </span>';
            })
            ->addColumn('payable', function ($shop) {
                return '<span class="amount-display text-primary fw-bold">
                ₹' . number_format($shop->payable_amount, 2) . '
            </span>';
            })
            ->addColumn('status_badge', function ($shop) {
                if ($shop->status === 'paid') {
                    return '<span class="status-badge badge-success">
                    <i class="mdi mdi-check-circle"></i> Paid
                </span>';
                } elseif ($shop->status === 'failed') {
                    return '<span class="status-badge badge-danger">
                    <i class="mdi mdi-alert-circle"></i> Failed
                </span>';
                } elseif ($shop->status === 'processing') {
                    return '<span class="status-badge badge-info">
                    <i class="mdi mdi-clock-outline"></i> Processing
                </span>';
                } else {
                    return '<span class="status-badge badge-warning">
                    <i class="mdi mdi-file-outline"></i> Generated
                </span>';
                }
            })
            ->addColumn('actions', function ($shop) {
                $encryptedId = Crypt::encrypt($shop->id);
                return '<div class="action-buttons">
      <a href="javascript:void(0);"
   class="action-icon update-status-btn"
   data-id="' . $encryptedId . '"
   data-status="generated"
   title="Mark as Generated"
   data-bs-toggle="tooltip">
    <img src="' . url('img/tick-icon.png') . '" alt="Mark as Generated" width="24" height="24">
</a>
    </div>';
            })
            ->rawColumns(['shop_name', 'amount', 'commission', 'payable', 'status_badge', 'actions'])
            ->make(true);
    }

    public function markAsPaid(Request $request, Payout $payout)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        if ($payout->isPaid()) {
            return back()->with('error', 'This payout has already been marked as paid');
        }

        DB::beginTransaction();
        try {

            // Mark all payout shops as paid
            $payout->payoutShops()->update(['status' => PayoutShop::STATUS_PAID]);

            // Mark master payout as paid
            $payout->markAsPaid($validated['notes'] ?? null);


            DB::commit();

            return back()->with('success', 'Payout marked as paid successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark payout as paid: ' . $e->getMessage());
        }
    }

    public function markAsFailed(Request $request, Payout $payout)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:500'
        ]);

        if ($payout->isPaid()) {
            return back()->with('error', 'Paid payouts cannot be marked as failed');
        }

        DB::beginTransaction();

        try {
            // Mark all payout shops as failed
            $payout->payoutShops()->update(['status' => PayoutShop::STATUS_FAILED]);

            // Mark master payout as failed
            $payout->markAsFailed($validated['notes']);

            DB::commit();

            return back()->with('success', 'Payout marked as failed');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark payout as failed: ' . $e->getMessage());
        }
    }

    public function export(Payout $payout)
    {
        $payout->load(['payoutShops.shop']);

        $filename = 'payout_' . $payout->start_date->format('Y-m-d') . '_to_' . $payout->end_date->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        $callback = function () use ($payout) {
            $file = fopen('php://output', 'w');

            // Add CSV headers with bank details
            fputcsv($file, [
                'Shop ID',
                'Shop Name',
                'Amount',
                'Commission',
                'Payable Amount',
                'Bank Name',
                'Account Number',
                'IFSC Code',
                'Branch',
                'Account Holder Name',
            ]);

            foreach ($payout->payoutShops as $payoutShop) {
                fputcsv($file, [
                    $payoutShop->shop_id,
                    $payoutShop->shop->name ?? '',
                    $payoutShop->amount,
                    $payoutShop->commission,
                    $payoutShop->payable_amount,
                    $payoutShop->bank_name ?? '',
                    $payoutShop->account_number ?? '',
                    $payoutShop->ifsc_code ?? '',
                    $payoutShop->branch ?? '',
                    $payoutShop->account_holder_name ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function updateShopStatus(Request $request, PayoutShop $payoutShop)
    {
        $validated = $request->validate([
            'status' => 'required|in:generated,processing,paid,failed',
            'notes' => 'nullable|string|max:500'
        ]);

        $payoutShop->update(['status' => $validated['status']]);

        // Update master payout status if needed
        $payout = $payoutShop->payout;
        $this->updateMasterPayoutStatus($payout);

        return back()->with('success', 'Payout shop status updated successfully');
    }

    protected function updateMasterPayoutStatus(Payout $payout)
    {
        $statusCounts = $payout->payoutShops()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        if ($statusCounts->get(PayoutShop::STATUS_FAILED, 0) > 0) {
            $payout->markAsFailed('Some shops failed processing');
        } elseif ($statusCounts->get(PayoutShop::STATUS_PAID, 0) === $payout->payoutShops()->count()) {
            $payout->markAsPaid(auth()->id(), 'All shops paid successfully');
        } elseif ($statusCounts->get(PayoutShop::STATUS_PROCESSING, 0) > 0) {
            $payout->markAsProcessing();
        } else {
            $payout->update(['status' => Payout::STATUS_GENERATED]);
        }
    }
}
