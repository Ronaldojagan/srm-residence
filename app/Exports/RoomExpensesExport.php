<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class RoomExpensesExport implements FromArray, WithHeadings, WithEvents, WithTitle
{
    use BaseSRMExport;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $query = Expense::where('expense_type', 'room')->with(['room', 'creator'])->latest('expense_date');

        if ($this->request->filled('room_id') && $this->request->room_id !== 'all') {
            $query->where('room_id', $this->request->room_id);
        }
        if ($this->request->filled('category') && $this->request->category !== 'all') {
            $query->where('category', $this->request->category);
        }
        if ($this->request->filled('payment_method') && $this->request->payment_method !== 'all') {
            $query->where('payment_method', $this->request->payment_method);
        }
        if ($this->request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $this->request->start_date);
        }
        if ($this->request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $this->request->end_date);
        }
        if ($this->request->filled('search')) {
            $s = trim($this->request->search);
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('category', 'like', "%{$s}%")
                    ->orWhere('paid_to', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhereHas('room', fn($rq) => $rq->where('room_number', 'like', "%{$s}%"));
            });
        }

        $expenses = $query->get();

        $rows = [];
        $totalAmount = 0;

        foreach ($expenses as $idx => $e) {
            $amt = (float) $e->amount;
            $totalAmount += $amt;

            $rows[] = [
                $idx + 1,
                $e->expense_date ? $e->expense_date->format('Y-m-d') : '',
                $e->room ? $e->room->room_number : 'Common Area',
                $e->room ? $e->room->floor : '-',
                $e->category ?: 'General',
                $e->title,
                number_format($amt, 2, '.', ''),
                $e->payment_method ?: 'Cash',
                $e->paid_to ?: '-',
                $e->creator ? $e->creator->name : 'Admin',
                $e->notes ?: '',
            ];
        }

        // LAST ROW: TOTAL AMOUNTS
        $rows[] = [
            'TOTAL',
            '',
            count($expenses) . ' Expenses',
            '',
            '',
            '',
            number_format($totalAmount, 2, '.', ''),
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
            'Expense Date',
            'Room Number',
            'Floor',
            'Category',
            'Title / Purpose',
            'Amount (INR)',
            'Payment Method',
            'Paid To / Vendor',
            'Recorded By',
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
        return 'Room Expenses';
    }
}
