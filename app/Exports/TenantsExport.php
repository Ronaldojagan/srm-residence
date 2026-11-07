<?php

namespace App\Exports;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class TenantsExport implements FromArray, WithHeadings, WithEvents, WithTitle
{
    use BaseSRMExport;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $query = Tenant::with(['room', 'rooms', 'bills']);

        if ($this->request->filled('status') && $this->request->status !== 'all') {
            $query->where('status', $this->request->status === 'active' ? 1 : 0);
        }
        if ($this->request->filled('room_id') && $this->request->room_id !== 'all') {
            $query->where(function ($q) {
                $q->where('room_id', $this->request->room_id)
                    ->orWhereHas('rooms', fn($rq) => $rq->where('rooms.id', $this->request->room_id));
            });
        }
        if ($this->request->filled('search')) {
            $s = trim($this->request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('room_number', 'like', "%{$s}%");
            });
        }

        $query->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC');
        $tenants = $query->get();

        $rows = [];
        $totalRent = 0;
        $totalDeposit = 0;
        $totalInvoiced = 0;
        $totalPaid = 0;
        $totalDue = 0;

        foreach ($tenants as $idx => $t) {
            $rooms = $t->assigned_rooms;
            $roomNos = $rooms->isNotEmpty() ? $rooms->pluck('room_number')->implode(', ') : ($t->room_number ?? 'N/A');
            $floors = $rooms->isNotEmpty() ? $rooms->pluck('floor')->filter()->unique()->implode(', ') : ($t->room->floor ?? '');
            $types = $rooms->isNotEmpty() ? $rooms->pluck('room_type')->filter()->unique()->implode(', ') : ($t->room_type ?? '');

            $rent = (float) $t->monthly_rent;
            $deposit = (float) $t->security_deposit;
            $inv = (float) $t->bills->sum('amount');
            $paid = (float) $t->bills->sum('paid_amount');
            $due = (float) $t->bills->sum('due_amount');

            $totalRent += $rent;
            $totalDeposit += $deposit;
            $totalInvoiced += $inv;
            $totalPaid += $paid;
            $totalDue += $due;

            $rows[] = [
                $idx + 1,
                $t->name,
                $t->phone ?: '-',
                $t->email ?: '-',
                $t->emergency_contact ?: '-',
                $roomNos,
                $floors,
                $types,
                $t->commence_date ? $t->commence_date->format('Y-m-d') : '-',
                number_format($rent, 2, '.', ''),
                number_format($deposit, 2, '.', ''),
                $t->eb_start_reading ?? '0',
                number_format($inv, 2, '.', ''),
                number_format($paid, 2, '.', ''),
                number_format($due, 2, '.', ''),
                $t->status ? 'Active' : 'Inactive',
            ];
        }

        // LAST ROW: TOTAL AMOUNTS
        $rows[] = [
            'TOTAL',
            count($tenants) . ' Residents',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            number_format($totalRent, 2, '.', ''),
            number_format($totalDeposit, 2, '.', ''),
            '',
            number_format($totalInvoiced, 2, '.', ''),
            number_format($totalPaid, 2, '.', ''),
            number_format($totalDue, 2, '.', ''),
            '',
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Resident Name',
            'Phone Number',
            'Email Address',
            'Emergency Contact',
            'Assigned Room(s)',
            'Floor',
            'Room Type',
            'Commence Date',
            'Monthly Rent (INR)',
            'Security Deposit (INR)',
            'Initial EB Reading',
            'Total Invoiced (INR)',
            'Total Paid (INR)',
            'Current Dues (INR)',
            'Status',
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
        return 'Tenants Directory';
    }
}
