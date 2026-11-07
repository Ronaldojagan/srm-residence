<?php

namespace App\Exports;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PersonalExpensesExport implements FromArray, WithHeadings, WithEvents, WithTitle
{
    use BaseSRMExport;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $query = Expense::where('expense_type', 'personal')->with('creator')->latest('expense_date');

        if ($this->request->filled('month')) {
            $monthVal = $this->request->month;
            if ($monthVal !== 'all') {
                if (preg_match('/^\d{4}-\d{2}$/', $monthVal)) {
                    $carbonMonth = Carbon::createFromFormat('Y-m', $monthVal);
                    $query->where(function ($q) use ($carbonMonth, $monthVal) {
                        $q->where('expense_month', $carbonMonth->format('F Y'))
                            ->orWhere('expense_month', $monthVal)
                            ->orWhere(function ($sub) use ($carbonMonth) {
                                $sub->whereYear('expense_date', $carbonMonth->year)
                                    ->whereMonth('expense_date', $carbonMonth->month);
                            });
                    });
                } else {
                    $query->where('expense_month', $monthVal);
                }
            }
        }

        if ($this->request->filled('paid_to')) {
            $query->where('paid_to', 'like', '%' . trim($this->request->paid_to) . '%');
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
                    ->orWhere('paid_to', 'like', "%{$s}%")
                    ->orWhere('expense_month', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        $expenses = $query->get();

        $rows = [];
        $totalQty = 0;
        $totalLineItemsAmt = 0;
        $totalOverallAmt = 0;
        $rowIndex = 1;

        foreach ($expenses as $e) {
            $overallAmt = (float) $e->amount;
            $totalOverallAmt += $overallAmt;
            $hasItems = is_array($e->items) && count($e->items) > 0;

            if ($hasItems) {
                foreach ($e->items as $idx => $it) {
                    $lineSno = !empty($it['sno']) ? $it['sno'] : ($idx + 1);
                    $lineTitle = $it['title'] ?? '-';
                    $lineQty = isset($it['qty']) ? (float)$it['qty'] : 1;
                    $lineRate = isset($it['rate']) ? number_format((float)$it['rate'], 2, '.', '') : '-';
                    $lineAmt = isset($it['amount']) ? (float)$it['amount'] : 0.00;
                    $lineNotes = $it['notes'] ?? '';

                    $totalQty += $lineQty;
                    $totalLineItemsAmt += $lineAmt;

                    $rows[] = [
                        $rowIndex++,
                        $e->expense_month ?: ($e->expense_date ? $e->expense_date->format('F Y') : '-'),
                        $e->expense_date ? $e->expense_date->format('Y-m-d') : '-',
                        $e->title,
                        $e->paid_to ?: '-',
                        $lineSno,
                        $lineTitle,
                        $lineQty,
                        $lineRate,
                        number_format($lineAmt, 2, '.', ''),
                        $lineNotes ?: '-',
                        number_format($overallAmt, 2, '.', ''),
                        $e->notes ?: '',
                        $e->creator ? $e->creator->name : 'Admin',
                    ];
                }
            } else {
                $totalQty += 1;
                $totalLineItemsAmt += $overallAmt;

                $rows[] = [
                    $rowIndex++,
                    $e->expense_month ?: ($e->expense_date ? $e->expense_date->format('F Y') : '-'),
                    $e->expense_date ? $e->expense_date->format('Y-m-d') : '-',
                    $e->title,
                    $e->paid_to ?: '-',
                    '-',
                    $e->title,
                    '1',
                    number_format($overallAmt, 2, '.', ''),
                    number_format($overallAmt, 2, '.', ''),
                    '-',
                    number_format($overallAmt, 2, '.', ''),
                    $e->notes ?: '',
                    $e->creator ? $e->creator->name : 'Admin',
                ];
            }
        }

        // LAST ROW: TOTAL AMOUNTS
        $rows[] = [
            'TOTAL',
            '',
            '',
            count($expenses) . ' Expenses',
            '',
            '',
            ($rowIndex - 1) . ' Items',
            number_format($totalQty, 2, '.', ''),
            '',
            number_format($totalLineItemsAmt, 2, '.', ''),
            '',
            number_format($totalOverallAmt, 2, '.', ''),
            '',
            '',
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Expense Month',
            'Expense Date',
            'Main Title / Summary',
            'Paid To / Beneficiary',
            'Item S.No',
            'Line Item Title',
            'Quantity',
            'Unit Rate (INR)',
            'Line Item Amount (INR)',
            'Line Item Notes',
            'Overall Expense Total (INR)',
            'General Remarks',
            'Recorded By',
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
        return 'Personal Expenses';
    }
}
