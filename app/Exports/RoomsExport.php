<?php

namespace App\Exports;

use App\Models\Room;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class RoomsExport implements FromArray, WithHeadings, WithEvents, WithTitle
{
    use BaseSRMExport;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $query = Room::with(['currentTenant', 'activeTenants']);

        if ($this->request->filled('floor') && $this->request->floor !== 'all') {
            $query->where('floor', $this->request->floor);
        }
        if ($this->request->filled('occupancy_status') && $this->request->occupancy_status !== 'all') {
            if ($this->request->occupancy_status === 'self') {
                $query->where(function ($q) {
                    $q->where('occupancy_status', 'self')->orWhere('is_self', 1);
                });
            } else {
                $query->where('occupancy_status', $this->request->occupancy_status);
            }
        }
        if ($this->request->filled('room_type') && $this->request->room_type !== 'all') {
            $query->where('room_type', $this->request->room_type);
        }
        if ($this->request->filled('search')) {
            $s = trim($this->request->search);
            $query->where(function ($q) use ($s) {
                $q->where('room_number', 'like', "%{$s}%")
                    ->orWhere('floor', 'like', "%{$s}%")
                    ->orWhere('room_type', 'like', "%{$s}%")
                    ->orWhereHas('activeTenants', function ($tq) use ($s) {
                        $tq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%");
                    });
            });
        }

        $query->orderByRaw('CAST(room_number AS UNSIGNED) ASC, room_number ASC');
        $rooms = $query->get();

        $rows = [];
        $totalCapacity = 0;
        $totalRent = 0;
        $totalMaint = 0;
        $totalOther = 0;
        $totalExpected = 0;
        $totalDeposit = 0;

        foreach ($rooms as $idx => $r) {
            $residentNames = $r->activeTenants->isNotEmpty()
                ? $r->activeTenants->pluck('name')->implode(', ')
                : ($r->currentTenant ? $r->currentTenant->name : 'Vacant');

            $residentPhones = $r->activeTenants->isNotEmpty()
                ? $r->activeTenants->pluck('phone')->filter()->implode(', ')
                : ($r->currentTenant ? $r->currentTenant->phone : '-');

            $statusText = $r->is_self ? 'Self Occupied' : ucfirst($r->occupancy_status);

            $cap = (int) $r->capacity;
            $rent = (float) $r->monthly_rent;
            $maint = (float) $r->maintenance_charges;
            $other = (float) $r->other_charges;
            $expected = (float) $r->total_monthly;
            $deposit = (float) $r->security_deposit;

            $totalCapacity += $cap;
            $totalRent += $rent;
            $totalMaint += $maint;
            $totalOther += $other;
            $totalExpected += $expected;
            $totalDeposit += $deposit;

            $rows[] = [
                $idx + 1,
                $r->room_number,
                $r->floor,
                $r->room_type,
                $r->capacity,
                number_format($rent, 2, '.', ''),
                number_format($r->eb_unit_rate, 2, '.', ''),
                number_format($maint, 2, '.', ''),
                number_format($other, 2, '.', ''),
                number_format($expected, 2, '.', ''),
                number_format($deposit, 2, '.', ''),
                $statusText,
                $r->is_self ? 'Yes' : 'No',
                $residentNames,
                $residentPhones,
            ];
        }

        // LAST ROW: TOTAL AMOUNTS
        $rows[] = [
            'TOTAL',
            count($rooms) . ' Rooms',
            '',
            '',
            $totalCapacity . ' Beds',
            number_format($totalRent, 2, '.', ''),
            '',
            number_format($totalMaint, 2, '.', ''),
            number_format($totalOther, 2, '.', ''),
            number_format($totalExpected, 2, '.', ''),
            number_format($totalDeposit, 2, '.', ''),
            '',
            '',
            '',
            '',
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Room Number',
            'Floor',
            'Room Type',
            'Capacity',
            'Monthly Rent (INR)',
            'EB Rate (INR/unit)',
            'Maintenance Charges (INR)',
            'Other Charges (INR)',
            'Total Monthly Expected (INR)',
            'Security Deposit (INR)',
            'Occupancy Status',
            'Self Occupied',
            'Current Resident(s)',
            'Resident Phone',
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
        return 'Room Inventory';
    }
}
