<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class UsersExport implements FromArray, WithHeadings, WithEvents, WithTitle
{
    use BaseSRMExport;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $query = User::orderBy('id', 'desc');

        if ($this->request->filled('type') && $this->request->type !== 'all') {
            $query->where('type', $this->request->type);
        }
        if ($this->request->filled('status') && $this->request->status !== 'all') {
            $query->where('status', $this->request->status === 'active' ? 1 : 0);
        }
        if ($this->request->filled('search')) {
            $s = trim($this->request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('type', 'like', "%{$s}%");
            });
        }

        $users = $query->get();
        $rows = [];

        foreach ($users as $idx => $u) {
            $roleLabel = 'Standard User';
            if ($u->type === 'Admin' || $u->type === \App\Enums\UserType::SUPERADMIN) {
                $roleLabel = 'Administrator';
            } elseif ($u->type === 'delivery' || strtolower($u->type) === 'staff') {
                $roleLabel = 'Staff / Manager';
            }

            $rows[] = [
                $idx + 1,
                $u->name,
                $u->email,
                $u->phone ?: '-',
                $roleLabel,
                $u->status == 1 ? 'Active' : 'Inactive',
                $u->created_at ? $u->created_at->format('Y-m-d H:i') : '-',
            ];
        }

        // LAST ROW: TOTAL SUMMARY
        $rows[] = [
            'TOTAL',
            count($users) . ' User Accounts',
            '',
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
            'Full Name',
            'Email Address',
            'Phone Number',
            'Role / Type',
            'Status',
            'Created Date',
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
        return 'System Users';
    }
}
