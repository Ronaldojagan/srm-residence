<?php

namespace App\Exports;

use App\Models\Order;
use App\Enums\OrderStatus;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $orderIds;
    protected $includeAllData;

    public function __construct($orderIds = null, $includeAllData = true)
    {
        $this->orderIds = $orderIds;
        $this->includeAllData = $includeAllData;
    }

    public function collection()
    {
        $query = Order::with([
            'user',
            'payment',
            'shippingAddress',
            'coupon',
            'orderItems' => function($query) {
                $query->with(['product', 'shop']);
            },
            'orderItems.shop',
            'statusHistory',
            'cancelledBy',
            'originalOrder',
            'replacementOrders',
            'precheckout',
            'feedback'
        ])
        ->orderBy('created_at', 'desc');

        if ($this->orderIds) {
            $query->whereIn('id', $this->orderIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        if (!$this->includeAllData) {
            return [
                'Order Number',
                'Date',
                'Customer Name',
                'Customer Phone',
                'Shops',
                'Total Amount',
                'Status',
                'Payment Method',
                'Payment Status',
                'Cancellation Reason',
                'Created At'
            ];
        }

        return [
            'Order Number',
            'Order Type',
            'Invoice Number',

            'Customer Name',
            'Customer Email',
            'Customer Phone',

            'Order Date',
            'Completed Date',
            'Cancelled Date',
            'Delivery Completed Date',

            'Total Amount',
            'Discount Amount',
            'Final Amount',
            'Tax Amount',
            'Shipping Amount',

            'Payment Method',
            'Payment Status',
            'Payment Date',
            'Transaction ID',
            'Merchant Transaction ID',
            'Refund Amount',
            'Refunded At',

            'Order Status',
            'Status Description',
            'Is Active Order',

            'Shipping Name',
            'Shipping Phone',
            'Full Address',
            'Flat/House No',
            'Street',
            'Area',
            'City',
            'District',
            'Pincode',
            'Address Type',
            'Is Default Address',

            'Total Items',
            'Total Quantity',
            'Items List',
            'Shops Count',
            'Shops List',

            'Has Shipments',
            'Shipments Count',
            'Delivered Shipments',
            'All Items Delivered',

            'Is Replacement Order',
            'Original Order Number',
            'Replacement Notes',
            'Replacement Orders Count',

            'Coupon Code',
            'Coupon Type',
            'Coupon Discount',
            'Coupon Min Order',
            'Coupon Max Discount',

            'Precheckout Status',

            'Cancellation Reason',
            'Cancelled By Name',
            'Cancelled By Email',

            'Completion Code',
            'Is Verified',

            'Status Changes Count',
            'Latest Status Change',

            'Has Feedback',
            'Feedback Count',
            'Average Rating',
        ];
    }

    public function map($order): array
    {
        if (!$this->includeAllData) {
            $shops = [];
            foreach ($order->orderItems as $item) {
                if ($item->shop) {
                    $shops[] = $item->shop->name;
                }
            }
            $shopNames = !empty($shops) ? implode(', ', array_unique($shops)) : 'N/A';

            $customerPhone = $order->user->phone ?? 'N/A';
            if ($customerPhone !== 'N/A') {
                $customerPhone = "" . $customerPhone;
            }

            return [
                $order->order_no,
                $order->date?->format('Y-m-d H:i:s') ?? $order->created_at->format('Y-m-d H:i:s'),
                $order->user->name ?? 'N/A',
                $customerPhone,
                $shopNames,
                number_format($order->total_amount, 2),
                $order->status,
                $order->payment->payment_method ?? 'N/A',
                $order->payment->status ?? 'N/A',
                $order->cancellation_reason ?? 'N/A',
                $order->created_at->format('Y-m-d H:i:s')
            ];
        }

        $itemsList = [];
        $shopsList = [];
        $totalQuantity = 0;
        $shopIds = [];

        foreach ($order->orderItems as $item) {
            $totalQuantity += $item->quantity;
            $itemsList[] = sprintf(
                "%s x%d @ ₹%s = ₹%s | Status: %s",
                $item->product_name,
                $item->quantity,
                number_format($item->amount, 2),
                number_format($item->total_amount, 2),
                $item->status_description
            );

            if ($item->shop_id && !in_array($item->shop_id, $shopIds)) {
                $shopIds[] = $item->shop_id;
                $shopsList[] = $item->shop->name ?? 'Unknown Shop';
            }
        }

        $shippingAddress = $order->shippingAddress;

        $shippingPhone = $shippingAddress->phone ?? 'N/A';
        if ($shippingPhone !== 'N/A') {
            $shippingPhone = "" . $shippingPhone;
        }

        $shippingDetails = [
            'name' => $shippingAddress->name ?? 'N/A',
            'phone' => $shippingPhone,
            'full_address' => $shippingAddress->full_address ?? 'N/A',
            'flat_no' => $shippingAddress->flat_no ?? 'N/A',
            'street' => $shippingAddress->street ?? 'N/A',
            'area_name' => $shippingAddress->area_name ?? 'N/A',
            'city' => $shippingAddress->city ?? 'N/A',
            'district' => $shippingAddress->district ?? 'N/A',
            'pincode' => $shippingAddress->pincode ?? 'N/A',
            'address_type' => $shippingAddress->address_type ?? 'N/A',
            'is_default' => isset($shippingAddress->is_default) ? ($shippingAddress->is_default ? 'Yes' : 'No') : 'N/A'
        ];

        $payment = $order->payment;
        $paymentDetails = [
            'method' => $payment->payment_method ?? 'N/A',
            'status' => $payment->status ?? 'N/A',
            'date' => $payment?->date?->format('Y-m-d H:i:s') ?? 'N/A',
            'transaction_id' => $payment->transaction_id ?? 'N/A',
            'merchant_transaction_id' => $payment->merchant_transaction_id ?? 'N/A',
            'refund_amount' => isset($payment->refund_amount) ? number_format($payment->refund_amount, 2) : '0.00',
            'refunded_at' => $payment?->refunded_at?->format('Y-m-d H:i:s') ?? 'N/A',
        ];

        $coupon = $order->coupon;
        $couponDetails = [
            'code' => $coupon->code ?? 'N/A',
            'type' => $coupon->type ?? 'N/A',
            'discount' => isset($coupon->discount_amount) ? number_format($coupon->discount_amount, 2) : '0.00',
            'min_order' => isset($coupon->min_order_amount) ? number_format($coupon->min_order_amount, 2) : 'N/A',
            'max_discount' => isset($coupon->max_discount) ? number_format($coupon->max_discount, 2) : 'N/A'
        ];

        $cancelledBy = $order->cancelledBy;
        $cancellationDetails = [
            'name' => $cancelledBy->name ?? 'N/A',
            'email' => $cancelledBy->email ?? 'N/A'
        ];

        $originalOrder = $order->originalOrder;
        $originalOrderDetails = [
            'order_no' => $originalOrder->order_no ?? 'N/A'
        ];

        $precheckout = $order->precheckout;
        $precheckoutDetails = [
            'status' => $precheckout->status ?? 'N/A'
        ];

        $statusDescription = OrderStatus::getDescription($order->status) ?? $order->status;

        $feedback = $order->feedback;
        $feedbackDetails = [
            'has_feedback' => $feedback->count() > 0 ? 'Yes' : 'No',
            'count' => $feedback->count(),
            'avg_rating' => $feedback->avg('rating') ? number_format($feedback->avg('rating'), 1) : 'N/A'
        ];

        $statusHistory = $order->statusHistory;
        $latestStatusChange = $statusHistory->sortByDesc('created_at')->first();

        $customerPhone = $order->user->phone ?? 'N/A';
        if ($customerPhone !== 'N/A') {
            $customerPhone = "" . $customerPhone;
        }

        return [
            $order->order_no,
            $order->order_type ?? 'Replace',
            $order->invoice_no ?? 'N/A',

            $order->user->name ?? 'N/A',
            $order->user->email ?? 'N/A',
            $customerPhone,

            $order->date?->format('Y-m-d H:i:s') ?? $order->created_at->format('Y-m-d H:i:s'),
            $order->completed_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $order->cancelled_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $order->delivery_completed_at?->format('Y-m-d H:i:s') ?? 'N/A',

            number_format($order->total_amount, 2),
            isset($order->discount_amount) ? number_format($order->discount_amount, 2) : '0.00',
            isset($order->final_amount) ? number_format($order->final_amount, 2) : number_format($order->total_amount, 2),
            isset($order->tax_amount) ? number_format($order->tax_amount, 2) : '0.00',
            isset($order->shipping_amount) ? number_format($order->shipping_amount, 2) : '0.00',

            $paymentDetails['method'],
            $paymentDetails['status'],
            $paymentDetails['date'],
            $paymentDetails['transaction_id'],
            $paymentDetails['merchant_transaction_id'],
            $paymentDetails['refund_amount'],
            $paymentDetails['refunded_at'],

            $order->status,
            $statusDescription,
            in_array($order->status, [OrderStatus::PENDING, OrderStatus::SHIPPED]) ? 'Yes' : 'No',

            $shippingDetails['name'],
            $shippingDetails['phone'],
            $shippingDetails['full_address'],
            $shippingDetails['flat_no'],
            $shippingDetails['street'],
            $shippingDetails['area_name'],
            $shippingDetails['city'],
            $shippingDetails['district'],
            $shippingDetails['pincode'],
            $shippingDetails['address_type'],
            $shippingDetails['is_default'],

            $order->orderItems->count(),
            $totalQuantity,
            implode("\n", $itemsList),
            count(array_unique($shopIds)),
            !empty($shopsList) ? implode(", ", $shopsList) : 'N/A',

            $order->has_shipments ? 'Yes' : 'No',
            $order->shipments_count ?? 0,
            $order->delivered_shipments_count ?? 0,
            $order->all_items_delivered ? 'Yes' : 'No',

            $order->is_replacement ? 'Yes' : 'No',
            $originalOrderDetails['order_no'],
            $order->replacement_notes ?? 'N/A',
            $order->replacementOrders->count(),

            $couponDetails['code'],
            $couponDetails['type'],
            $couponDetails['discount'],
            $couponDetails['min_order'],
            $couponDetails['max_discount'],

            $precheckoutDetails['status'],

            $order->cancellation_reason ?? 'N/A',
            $cancellationDetails['name'],
            $cancellationDetails['email'],

            $order->completion_code ?? 'N/A',
            $order->completion_code ? 'Yes' : 'No',

            $statusHistory->count(),
            $latestStatusChange ? $latestStatusChange->created_at->format('Y-m-d H:i:s') . ' - ' . $latestStatusChange->status : 'N/A',

            $feedbackDetails['has_feedback'],
            $feedbackDetails['count'],
            $feedbackDetails['avg_rating'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        foreach(range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $phoneColumns = ['D', 'S'];
        foreach ($phoneColumns as $col) {
            $sheet->getStyle($col)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        }

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3498DB']
                ],
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ],

            'J:N' => [
                'numberFormat' => [
                    'formatCode' => '₹#,##0.00'
                ]
            ],

            'R:U' => [
                'numberFormat' => [
                    'formatCode' => '₹#,##0.00'
                ]
            ],

            'G:J' => [
                'numberFormat' => [
                    'formatCode' => 'yyyy-mm-dd hh:mm:ss'
                ]
            ],

            'AL' => [
                'alignment' => [
                    'wrapText' => true
                ]
            ],
            'AO' => [
                'alignment' => [
                    'wrapText' => true
                ]
            ],

            'A2:'.$sheet->getHighestColumn().$sheet->getHighestRow() => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FA']
                ]
            ],

            'A3:'.$sheet->getHighestColumn().'3' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFFFF']
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Orders Export';
    }
}
