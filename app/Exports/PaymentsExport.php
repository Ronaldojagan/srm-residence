<?php

namespace App\Exports;

use App\Models\BillPayment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PaymentsExport implements FromArray, WithHeadings, WithEvents, WithTitle
{
    use BaseSRMExport;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $query = BillPayment::with(['bill.room', 'tenant.room', 'receiver'])->latest();

        if ($this->request->filled('tenant_id') && $this->request->tenant_id !== 'all') {
            $query->where('tenant_id', $this->request->tenant_id);
        }
        if ($this->request->filled('payment_method') && $this->request->payment_method !== 'all') {
            $query->where('payment_method', $this->request->payment_method);
        }
        if ($this->request->filled('search')) {
            $s = trim($this->request->search);
            $query->where(function ($q) use ($s) {
                $q->where('payment_number', 'like', "%{$s}%")
                    ->orWhere('transaction_id', 'like', "%{$s}%")
                    ->orWhere('payment_method', 'like', "%{$s}%")
                    ->orWhereHas('tenant', fn($tq) => $tq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"))
                    ->orWhereHas('bill', fn($bq) => $bq->where('bill_number', 'like', "%{$s}%"));
            });
        }

        $payments = $query->get();

        $rows = [];
        $totalPaid = 0;

        foreach ($payments as $idx => $p) {
            $room = $p->bill->room ?? ($p->tenant->room ?? null);
            $amt = (float) $p->amount;
            $totalPaid += $amt;

            $rows[] = [
                $idx + 1,
                $p->payment_number,
                $p->bill ? $p->bill->bill_number : 'N/A',
                $p->payment_date ? $p->payment_date->format('Y-m-d') : '',
                $p->tenant ? $p->tenant->name : 'Resident',
                $p->tenant ? $p->tenant->phone : '-',
                $room ? $room->room_number : '-',
                $room ? $room->floor : '-',
                $p->payment_method,
                $p->transaction_id ?: '-',
                number_format($amt, 2, '.', ''),
                $p->receiver ? $p->receiver->name : 'Admin',
                $p->notes ?: '',
            ];
        }

        // LAST ROW: TOTAL AMOUNTS
        $rows[] = [
            'TOTAL',
            count($payments) . ' Receipts',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            number_format($totalPaid, 2, '.', ''),
            '',
            '',
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Receipt Number',
            'Invoice Reference',
            'Payment Date',
            'Resident Name',
            'Resident Phone',
            'Room Number',
            'Floor',
            'Payment Method',
            'Transaction ID',
            'Amount Paid (INR)',
            'Received By',
            'Notes',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->formatSheet($sheet, $sheet->getHighestRow(), $sheet->getHighestColumn());
            },
        ];
    }

    public function title(): string
    {
        return 'Payment Receipts';
    }
}
