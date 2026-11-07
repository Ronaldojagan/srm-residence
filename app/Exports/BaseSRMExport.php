<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait BaseSRMExport
{
    /**
     * Apply generous column widths, professional header styling, and prominent total row styling.
     */
    protected function formatSheet(Worksheet $sheet, int $highestRow, string $highestColumn)
    {
        // 1. Calculate base column widths
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->calculateColumnWidths();

        // 2. Expand every column with generous padding so headings and amounts never hide
        foreach (range('A', $highestColumn) as $col) {
            $calcWidth = $sheet->getColumnDimension($col)->getWidth();
            $sheet->getColumnDimension($col)->setAutoSize(false);
            // Minimum 18 width, plus generous +6 padding for breathing room
            $sheet->getColumnDimension($col)->setWidth(max(18, ceil($calcWidth) + 6));
        }

        // Specifically widen S.No to at least 10 and ensure wide amount columns
        $sheet->getColumnDimension('A')->setWidth(10);

        // 3. Header Row Styling (SRM Residence Signature Teal)
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0A5858'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'wrapText' => false,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '064040'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // 4. Data Rows Alignment & Zebra Striping
        if ($highestRow > 2) {
            for ($r = 2; $r < $highestRow; $r++) {
                $sheet->getRowDimension($r)->setRowHeight(22);
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$highestColumn}{$r}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC'],
                        ],
                    ]);
                }
            }
        }

        // 5. Total Row Styling (Prominent Golden Amber Accent, Bold, Double Bottom Border)
        if ($highestRow >= 2) {
            $sheet->getStyle("A{$highestRow}:{$highestColumn}{$highestRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => '0F172A'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FEF3C7'], // soft amber highlight
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                    'bottom' => [
                        'borderStyle' => Border::BORDER_DOUBLE,
                        'color' => ['rgb' => '0A5858'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension($highestRow)->setRowHeight(28);
        }
    }
}
