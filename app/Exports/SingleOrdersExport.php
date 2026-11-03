<?php

namespace App\Exports;

use App\Models\Order;
use App\Enums\OrderStatus;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SingleOrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents, ShouldAutoSize
{
    protected $orderId;
    protected $orderData;
    protected $rowCount = 1;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function query()
    {
        return Order::where('id', $this->orderId)->with([
            'user',
            'payment',
            'shippingAddress',
            'coupon',
            'orderItems.product',
            'orderItems.shop',
            'statusHistory',
            'cancelledBy',
            'originalOrder',
            'replacementOrders'
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function map($order): array
    {
        $this->orderData = $order;
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A:Z' => [
                'font' => [
                    'name' => 'Calibri',
                    'size' => 10
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $order = $this->orderData;

                if (!$order) {
                    return;
                }

                $sheet->getDefaultRowDimension()->setRowHeight(18);

                $sheet->mergeCells('A1:Z1');
                $sheet->setCellValue('A1', '');

                // ==================== MAIN HEADER ====================
                $this->rowCount = 1;
                $sheet->mergeCells("A{$this->rowCount}:L{$this->rowCount}");
                $sheet->setCellValue("A{$this->rowCount}", "ORDER DETAILS REPORT - #{$order->order_no}");
                $sheet->getStyle("A{$this->rowCount}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2C3E50']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1A252F']
                        ]
                    ]
                ]);
                $sheet->getRowDimension($this->rowCount)->setRowHeight(30);
                $this->rowCount++;

                // ==================== ORDER INFORMATION ====================
                $this->addSectionHeader($sheet, "ORDER INFORMATION");

                $orderInfo = [
                    ['Order Number:', $order->order_no],
                    ['Order Date:', $order->date?->format('d-m-Y H:i:s') ?? $order->created_at->format('d-m-Y H:i:s')],
                    ['Invoice Number:', $order->invoice_no ?? 'N/A'],
                    ['Order Status:', $order->status . ' (' . OrderStatus::getDescription($order->status) . ')'],
                    ['Order Type:', $order->is_replacement_order ? 'Replacement Order' : 'Regular Order']
                ];

                $this->addKeyValueTable($sheet, $orderInfo, 2);
                $this->rowCount++;

                // ==================== CUSTOMER INFORMATION ====================
                $this->addSectionHeader($sheet, "CUSTOMER INFORMATION");

                $customerInfo = [
                    ['Customer Name:', $order->user->name ?? 'N/A'],
                    ['Customer Email:', $order->user->email ?? 'N/A'],
                    ['Customer Phone:', $order->user->phone ? "" . $order->user->phone : 'N/A'],
                    ['Customer ID:', $order->user_id]
                ];

                $this->addKeyValueTable($sheet, $customerInfo, 2);
                $this->rowCount++;

                // ==================== FINANCIAL INFORMATION ====================
                $this->addSectionHeader($sheet, "FINANCIAL INFORMATION");

                $cancelledRefundTotal = 0;
                $returnedRefundTotal = 0;

                foreach ($order->orderItems as $item) {
                    if ($item->status == 'cancelled' && $item->refund_status == 'processed') {
                        $cancelledRefundTotal += (float) $item->refund_amount;
                    }
                    if ($item->status == 'returned' && $item->refund_status == 'processed') {
                        $returnedRefundTotal += (float) $item->refund_amount;
                    }
                }

                $totalRefunded = $cancelledRefundTotal + $returnedRefundTotal;
                $finalAmount = $order->total_amount - $totalRefunded;

                $financialInfo = [
                    ['Total Amount:', '₹' . number_format($order->total_amount, 2)],
                    ['Payment Method:', $order->payment->payment_method ?? 'N/A'],
                    ['Payment Status:', $order->payment->status ?? 'N/A'],
                    ['Transaction ID:', $order->payment->transaction_id ?? 'N/A']
                ];

                $this->addKeyValueTable($sheet, $financialInfo, 2);

                if ($totalRefunded > 0) {
                    $this->rowCount++;
                    $refundStartRow = $this->rowCount;

                    $refundInfo = [
                        ['Original Total:', '₹' . number_format($order->total_amount, 2)],
                        ['Cancelled Items Refund:', '- ₹' . number_format($cancelledRefundTotal, 2)],
                        ['Returned Items Refund:', '- ₹' . number_format($returnedRefundTotal, 2)],
                        ['Total Refunded:', '- ₹' . number_format($totalRefunded, 2)],
                        ['Final Amount:', '₹' . number_format($finalAmount, 2)]
                    ];

                    foreach ($refundInfo as $info) {
                        $sheet->setCellValue("A{$this->rowCount}", $info[0]);
                        $sheet->setCellValue("B{$this->rowCount}", $info[1]);

                        if (in_array($info[0], ['Cancelled Items Refund:', 'Returned Items Refund:', 'Total Refunded:'])) {
                            $sheet->getStyle("B{$this->rowCount}")->applyFromArray([
                                'font' => [
                                    'color' => ['rgb' => 'DC3545'],
                                    'bold' => true
                                ]
                            ]);
                        }

                        if ($info[0] == 'Final Amount:') {
                            $sheet->getStyle("A{$this->rowCount}:B{$this->rowCount}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'D4EDDA']
                                ],
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => '155724']
                                ],
                                'borders' => [
                                    'top' => [
                                        'borderStyle' => Border::BORDER_MEDIUM,
                                        'color' => ['rgb' => '28A745']
                                    ]
                                ]
                            ]);
                        }

                        $this->rowCount++;
                    }
                }

                $this->rowCount++;

                // ==================== SHIPPING INFORMATION ====================
                if ($order->shippingAddress) {
                    $this->addSectionHeader($sheet, "SHIPPING INFORMATION");

                    $shippingInfo = [
                        ['Recipient Name:', $order->shippingAddress->name ?? 'N/A'],
                        ['Phone:', $order->shippingAddress->phone ? "" . $order->shippingAddress->phone : 'N/A'],
                        ['Address:', $order->shippingAddress->full_address ?? 'N/A'],
                        ['City:', $order->shippingAddress->city ?? 'N/A'],
                        ['District:', $order->shippingAddress->district ?? 'N/A'],
                        ['Pincode:', $order->shippingAddress->pincode ?? 'N/A']
                    ];

                    $this->addKeyValueTable($sheet, $shippingInfo, 2);
                    $this->rowCount++;
                }

                // ==================== ORDER ITEMS SECTION ====================
                $this->addSectionHeader($sheet, "ORDER ITEMS DETAILS", true);
                $this->rowCount++;

                $itemsHeader = ['#', 'Product Name', 'Shop', 'Quantity', 'Unit Price', 'Total Price', 'Status', 'Refund Amount'];
                $headerRow = $this->rowCount;

                foreach ($itemsHeader as $index => $header) {
                    $col = chr(65 + $index);
                    $sheet->setCellValue("{$col}{$this->rowCount}", $header);
                    $sheet->getStyle("{$col}{$this->rowCount}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF']
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '34495E']
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'wrapText' => true
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '2C3E50']
                            ]
                        ]
                    ]);
                    $sheet->getColumnDimension($col)->setWidth(15);
                }

                $sheet->getRowDimension($this->rowCount)->setRowHeight(25);
                $this->rowCount++;

                $itemsStartRow = $this->rowCount;
                $statusColors = [
                    'pending' => 'FFD700',
                    'confirmed' => '3498DB',
                    'processing' => '9B59B6',
                    'shipped' => 'F39C12',
                    'delivered' => '27AE60',
                    'cancelled' => 'E74C3C',
                    'return_requested' => 'E67E22',
                    'returned' => '7F8C8D',
                    'refunded' => '1ABC9C',
                    'replace_requested' => 'D35400'
                ];

                $totalItems = $order->orderItems->count();
                $itemNumber = 1;

                foreach ($order->orderItems as $item) {
                    $refundAmount = '₹0.00';
                    if (in_array($item->status->value, ['cancelled', 'returned']) && $item->refund_status == 'processed') {
                        $refundAmount = '₹' . number_format($item->refund_amount, 2);
                    }

                    $rowData = [
                        $itemNumber,
                        $item->product_name,
                        $item->shop->name ?? 'N/A',
                        $item->quantity . ($item->quantity_unit ? ' ' . $item->quantity_unit : ''),
                        '₹' . number_format($item->amount, 2),
                        '₹' . number_format($item->total_amount, 2),
                        $item->status_description,
                        $refundAmount
                    ];

                    foreach ($rowData as $colIndex => $value) {
                        $col = chr(65 + $colIndex);
                        $cell = "{$col}{$this->rowCount}";
                        $sheet->setCellValue($cell, $value);

                        $fillColor = ($itemNumber % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                        $sheet->getStyle($cell)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $fillColor]
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'E0E0E0']
                                ]
                            ]
                        ]);

                        if (in_array($colIndex, [0, 3, 4, 5, 6, 7])) {
                            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }

                        if ($colIndex == 6) {
                            $status = strtolower($item->status->value);
                            $color = $statusColors[$status] ?? '95A5A6';

                            $sheet->getStyle($cell)->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => 'FFFFFF']
                                ],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $color]
                                ]
                            ]);
                        }

                        if ($colIndex == 7 && $refundAmount !== '₹0.00') {
                            $sheet->getStyle($cell)->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => 'DC3545']
                                ]
                            ]);
                        }
                    }

                    $itemNumber++;
                    $this->rowCount++;
                }

                $itemsEndRow = $this->rowCount - 1;

                $sheet->getStyle("A{$itemsEndRow}:H{$itemsEndRow}")->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '34495E']
                        ]
                    ]
                ]);

                // ==================== SUMMARY SECTION ====================
                $this->rowCount += 2;
                $this->addSectionHeader($sheet, "ORDER SUMMARY", true);

                $summaryInfo = [
                    ['Total Items:', $totalItems],
                    ['Total Quantity:', $order->orderItems->sum('quantity')],
                    ['Unique Shops:', $order->orderItems->groupBy('shop_id')->count()],
                    ['Original Order Value:', '₹' . number_format($order->total_amount, 2)],
                    ['Total Refunded:', '₹' . number_format($totalRefunded, 2)],
                    ['Final Amount Paid:', '₹' . number_format($finalAmount, 2)],
                    ['Payment Status:', $order->payment->status ?? 'Pending'],
                    ['Order Age:', $this->getOrderAge($order->created_at)]
                ];

                $summaryStartRow = $this->rowCount;
                $this->addKeyValueTable($sheet, $summaryInfo, 2, false);
                $summaryEndRow = $this->rowCount - 1;

                $summaryRange = "A{$summaryStartRow}:B{$summaryEndRow}";
                $sheet->getStyle($summaryRange)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'ECF0F1']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'BDC3C7']
                        ]
                    ]
                ]);

                $finalAmountRow = $summaryStartRow + 5;
                $sheet->getStyle("A{$finalAmountRow}:B{$finalAmountRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '155724']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D4EDDA']
                    ]
                ]);

                // ==================== ADDITIONAL SECTIONS ====================

                if ($order->coupon) {
                    $this->rowCount += 2;
                    $this->addSectionHeader($sheet, "COUPON INFORMATION");

                    $couponInfo = [
                        ['Coupon Code:', $order->coupon->code],
                        ['Discount Type:', ucfirst($order->coupon->type)],
                        ['Discount Amount:', '₹' . number_format($order->coupon->discount_amount, 2)]
                    ];

                    $this->addKeyValueTable($sheet, $couponInfo, 2);
                }

                if ($order->is_replacement_order) {
                    $this->rowCount += 2;
                    $this->addSectionHeader($sheet, "REPLACEMENT INFORMATION");

                    $replacementInfo = [
                        ['Original Order:', $order->originalOrder->order_no ?? 'N/A'],
                        ['Replacement Notes:', $order->replacement_notes ?? 'N/A'],
                        ['Replacement Date:', $order->created_at->format('d-m-Y H:i:s')]
                    ];

                    $this->addKeyValueTable($sheet, $replacementInfo, 2);
                }

                if ($order->cancellation_reason) {
                    $this->rowCount += 2;
                    $this->addSectionHeader($sheet, "CANCELLATION INFORMATION");

                    $cancellationInfo = [
                        ['Reason:', $order->cancellation_reason],
                        ['Cancelled By:', $order->cancelledBy->name ?? 'System'],
                        ['Cancelled At:', $order->cancelled_at?->format('d-m-Y H:i:s') ?? 'N/A'],
                        ['Refund Processed:', $order->payment->refund_id ? 'Yes (ID: ' . $order->payment->refund_id . ')' : 'No']
                    ];

                    $this->addKeyValueTable($sheet, $cancellationInfo, 2);
                }

                // ==================== FINAL TOUCHES ====================

                foreach (range('A', 'H') as $column) {
                    $dimension = $sheet->getColumnDimension($column);
                    $dimension->setAutoSize(true);
                    $currentWidth = $dimension->getWidth();
                    if ($currentWidth < 12) {
                        $dimension->setWidth(12);
                    }
                }

                $sheet->getStyle('B:B')->getAlignment()->setWrapText(true);

                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $lastRow = $this->rowCount + 2;
                $sheet->mergeCells("A{$lastRow}:H{$lastRow}");
                $sheet->setCellValue("A{$lastRow}", "Generated on: " . now()->format('d-m-Y H:i:s'));
                $sheet->getStyle("A{$lastRow}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'color' => ['rgb' => '7F8C8D']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT
                    ]
                ]);
            }
        ];
    }

    private function addSectionHeader(&$sheet, $title, $underline = false)
    {
        $sheet->mergeCells("A{$this->rowCount}:H{$this->rowCount}");
        $sheet->setCellValue("A{$this->rowCount}", $title);

        $style = [
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '2C3E50']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ECF0F1']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'BDC3C7']
                ]
            ]
        ];

        if ($underline) {
            $style['borders']['bottom'] = [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '3498DB']
            ];
        }

        $sheet->getStyle("A{$this->rowCount}")->applyFromArray($style);
        $sheet->getRowDimension($this->rowCount)->setRowHeight(25);
        $this->rowCount++;
    }

    private function addKeyValueTable(&$sheet, $pairs, $numColumns, $addRowHeight = true)
    {
        $startRow = $this->rowCount;

        foreach ($pairs as $pair) {
            $keyCell = "A{$this->rowCount}";
            $valueCell = "B{$this->rowCount}";

            $sheet->setCellValue($keyCell, $pair[0]);
            $sheet->setCellValue($valueCell, $pair[1]);

            $sheet->getStyle($keyCell)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FA']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E0E0E0']
                    ]
                ]
            ]);

            $sheet->getStyle($valueCell)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E0E0E0']
                    ]
                ]
            ]);

            if ($addRowHeight) {
                $sheet->getRowDimension($this->rowCount)->setRowHeight(20);
            }

            $this->rowCount++;
        }
    }

    private function getOrderAge($createdAt)
    {
        $now = now();
        $diff = $createdAt->diff($now);

        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
        } elseif ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
        } elseif ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        } else {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }
    }
}
