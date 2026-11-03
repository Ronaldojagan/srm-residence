<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->invoice_no }}</title>
    <style>
        @page {
            margin: 20px;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }

        .invoice-container {
            width: 100%;
            max-width: 794px;
            margin: 0 auto;
            padding: 15px;
            position: relative;
        }

        /* Header Section - DOM PDF optimized */
        .header {
            width: 95%;
            display: block;
            border-bottom: 2px solid #3F2B83;
            padding-bottom: 10px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .company-info {
            float: left;
            width: 60%;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #3F2B83;
            margin: 0 0 5px 0;
        }

        .company-details {
            font-size: 11px;
            line-height: 1.3;
        }

        .company-details p {
            margin: 2px 0;
        }

        .invoice-title {
            float: right;
            width: 35%;
        }

        .invoice-title h1 {
            font-size: 28px;
            color: #3F2B83;
            margin: 0 0 8px 0;
            font-weight: bold;
        }

        .invoice-meta {
            font-size: 11px;
        }

        .invoice-meta p {
            margin: 3px 0;
        }

        /* Status Badge - Fixed positioning for DOM PDF */
        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 100;
        }

        .status-confirmed {
            background: #4CAF50;
            color: white;
        }

        .status-pending {
            background: #FF9800;
            color: white;
        }

        .status-processing {
            background: #2196F3;
            color: white;
        }

        .status-shipped {
            background: #9C27B0;
            color: white;
        }

        .status-delivered {
            background: #4CAF50;
            color: white;
        }

        .status-cancelled {
            background: #f44336;
            color: white;
        }

        /* Clear floats */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Seller & Buyer Section - DOM PDF optimized */
        .parties-section {
            width: 100%;
            margin-bottom: 15px;
            display: block;
            overflow: hidden;
        }

        .party-card {
            float: left;
            width: 95%;
            padding: 10px;
            box-sizing: border-box;
            min-height: 150px;
        }

        .party-card:first-child {
            margin-right: 2%;
        }

        .party-card:last-child {
            margin-left: 2%;
        }

        .party-card h3 {
            color: #3F2B83;
            font-size: 13px;
            margin: 0 0 8px 0;
            padding-bottom: 4px;
        }

        .party-card p {
            margin: 4px 0;
            font-size: 11px;
            line-height: 1.3;
        }

        /* Order Details - DOM PDF optimized */
        .order-details {
            width: 100%;
            margin-bottom: 15px;
            display: block;
            overflow: hidden;
        }

        .detail-card {
            float: left;
            width: 48%;
            padding: 10px;
            border: 1px solid #ddd;
            background: #fff;
            box-sizing: border-box;
        }

        .detail-card:first-child {
            margin-right: 2%;
        }

        .detail-card:last-child {
            margin-left: 2%;
        }

        .detail-card h4 {
            color: #861717;
            font-size: 12px;
            margin: 0 0 8px 0;
        }

        .detail-row {
            margin-bottom: 5px;
            font-size: 11px;
            line-height: 1.3;
        }

        .detail-label {
            display: inline-block;
            width: 45%;
            font-weight: bold;
            color: #555;
        }

        .detail-value {
            display: inline-block;
            width: 52%;
            color: #333;
        }

        /* Shipping Address */
        .shipping-address {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        /* Products Table - DOM PDF optimized */
        .products-section {
            width: 95%;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .products-section h3 {
            color: #3F2B83;
            font-size: 13px;
            margin: 0 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #3F2B83;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            page-break-inside: auto;
        }

        .products-table th {
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }

        .products-table td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
            page-break-inside: avoid;
            page-break-before: auto;
        }

        .products-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .products-table tr td:last-child {
            page-break-before: avoid;
        }

        .product-image {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border: 1px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            width: 95%;
            margin-top: 15px;
        }

        .totals-table {
            width: 300px;
            float: right;
            border-collapse: collapse;
            font-size: 11px;
            page-break-inside: avoid;
        }

        .totals-table td {
            padding: 6px 10px;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }

        .totals-table tr:last-child {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .amount-column {
            text-align: right;
            font-family: DejaVu Sans Mono, monospace, DejaVu Sans;
        }

        .payment-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background: #4CAF50;
            color: white;
        }

        .status-pending {
            background: #FF9800;
            color: white;
        }

        .status-failed {
            background: #f44336;
            color: white;
        }

        /* Replacement Badge */
        .replacement-badge {
            background-color: #ff9900;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            margin-left: 5px;
        }

        /* Footer Section - DOM PDF optimized */
        .footer {
            width: 100%;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 2px solid #3F2B83;
            text-align: center;
            font-size: 10px;
            color: #666;
            page-break-before: avoid;
        }

        .terms {
            margin-top: 15px;
            font-size: 10px;
            line-height: 1.3;
            color: #666;
            page-break-inside: avoid;
        }

        .terms h4 {
            color: #333;
            font-size: 11px;
            margin-bottom: 5px;
        }

        .terms p {
            margin: 3px 0;
        }

        /* Authorized Signature */
        .authorized-sign {
            margin-top: 20px;
            text-align: right;
            page-break-inside: avoid;
        }

        .signature-line {
            width: 200px;
            border-top: 1px solid #333;
            margin: 20px 0 5px auto;
        }

        /* Watermark for replacement orders */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(255, 153, 0, 0.1);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
            width: 100%;
            text-align: center;
        }

        /* Page break handling for DOM PDF */
        .page-break {
            page-break-before: always;
        }

        .keep-together {
            page-break-inside: avoid;
        }

        .break-before {
            page-break-before: always;
        }

        .break-after {
            page-break-after: always;
        }

        /* DOM PDF specific: Ensure tables don't break awkwardly */
        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        td {
            page-break-inside: avoid;
            page-break-before: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        /* Print-specific styles for DOM PDF */
        @media print {
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .invoice-container {
                border: none;
                padding: 0;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            .watermark {
                opacity: 0.1;
            }

            /* Force background colors in print */
            .status-badge,
            .payment-status,
            .replacement-badge,
            .products-table th {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }

        /* Additional DOM PDF fixes */
        img {
            max-width: 100%;
            height: auto;
        }

        /* Force black text for print */
        .invoice-container * {
            color: #000 !important;
        }

        .products-table th,
        .products-table td,
        .totals-table td,
        .party-card,
        .detail-card {
            border-color: #000 !important;
        }
    </style>
</head>

<body>
    @if ($order->is_replacement_order)
        <div class="watermark">REPLACEMENT</div>
    @endif

    <div class="invoice-container">
        <div class="header clearfix">
            <div class="company-info">
                <div class="company-logo">
                    <img src="{{ public_path('img/logo.png') }}" alt="Logo" style="width: 100px;">
                </div>
            </div>

            <div class="invoice-title">
                <h1 style="margin-left: 70px;">INVOICE</h1>
                <div class="invoice-meta" style="text-align: center !important;">
                    <p><strong>Invoice No:</strong> {{ $order->invoice_no ?? $order->order_no }}</p>
                    <p><strong>Invoice Date:</strong>
                        {{ $order->date ? $order->date->format('d/m/Y') : $order->created_at->format('d/m/Y') }}</p>
                    <p><strong>Order No:</strong> {{ $order->order_no }}</p>
                    @if ($order->is_replacement_order)
                        <p><strong>Type:</strong> <span class="replacement-badge">REPLACEMENT</span></p>
                        @if ($order->originalOrder)
                            <p><strong>Original Order:</strong> #{{ $order->originalOrder->order_no }}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="parties-section clearfix">
            <div class="party-card">
                <h3>BILL TO</h3>
                <p><strong>Name:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $order->user->phone ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
                <p><strong>Address:</strong> {{ $order->shippingAddress->full_address }}</p>
            </div>

        </div>

        <div class="products-section keep-together">
            <h3>ORDER ITEMS</h3>
            <table class="products-table">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="10%">Image</th>
                        <th width="25%">Product Name</th>
                        <th width="15%">Seller</th>
                        <th width="10%">Quantity</th>
                        <th width="15%">Unit Price</th>
                        <th width="20%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderItems as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">
                                @if ($item->product_image && $item->product_image != 'avatar.jpg')
                                    <img src="{{ public_path('storage/product/' . $item->product_image) }}"
                                        class="product-image" alt="{{ $item->product_name }}">
                                @else
                                    <div
                                        style="width: 40px; height: 40px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 9px; margin: 0 auto;">
                                        No Image
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                @if ($item->description)
                                    <br><small
                                        style="font-size: 10px;">{{ Str::limit($item->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($item->product && $item->product->shop)
                                    {{ $item->product->shop->name ?? 'N/A' }}
                                @else
                                    {{ $company['name'] }}
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">
                                @if ($order->is_replacement_order)
                                    <span style="color: #ff9900; font-weight: bold;">FREE</span>
                                @else
                                    ₹{{ number_format($item->amount, 2) }}
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($order->is_replacement_order)
                                    <span style="color: #ff9900; font-weight: bold;">FREE</span>
                                @else
                                    ₹{{ number_format($item->total_amount, 2) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Section -->
        <div class="totals-section clearfix">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="amount-column">
                        @if ($order->is_replacement_order)
                            ₹0.00
                        @else
                            ₹{{ number_format($totals['subtotal'], 2) }}
                        @endif
                    </td>
                </tr>

                @if (!$order->is_replacement_order)
                    @if ($totals['discount'] > 0)
                        <tr>
                            <td>Discount:</td>
                            <td class="amount-column" style="color: green;">
                                -₹{{ number_format($totals['discount'], 2) }}</td>
                        </tr>
                    @endif

                    @if ($totals['delivery'] > 0)
                        <tr>
                            <td>Delivery Charge:</td>
                            <td class="amount-column">₹{{ number_format($totals['delivery'], 2) }}</td>
                        </tr>
                    @endif

                    @if ($totals['platform_fee'] > 0)
                        <tr>
                            <td>Platform Fee:</td>
                            <td class="amount-column">₹{{ number_format($totals['platform_fee'], 2) }}</td>
                        </tr>
                    @endif

                    @if ($totals['tax'] > 0)
                        <tr>
                            <td>Tax (GST):</td>
                            <td class="amount-column">₹{{ number_format($totals['tax'], 2) }}</td>
                        </tr>
                    @endif
                @endif

                <tr style="border-top: 2px solid #333;">
                    <td><strong>Grand Total:</strong></td>
                    <td class="amount-column">
                        @if ($order->is_replacement_order)
                            <strong style="color: #ff9900;">₹0.00 (FREE)</strong>
                        @else
                            <strong>₹{{ number_format($totals['grand_total'], 2) }}</strong>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        @if ($order->is_replacement_order && $order->replacement_notes)
            <div class="detail-card"
                style="margin-top: 15px; background: #fff8e1; border: 1px solid #ff9900; clear: both;">
                <h4 style="color: #ff9900;">REPLACEMENT NOTES</h4>
                <p>{{ $order->replacement_notes }}</p>
                @if ($order->originalOrder)
                    <p><strong>Original Order:</strong> #{{ $order->originalOrder->order_no }}
                        ({{ $order->originalOrder->created_at->format('d M Y') }})</p>
                @endif
            </div>
        @endif

        <div class="terms keep-together">
            <h4>TERMS & CONDITIONS</h4>
            <p>1. This is a computer-generated invoice and does not require a physical signature.</p>
            <p>2. Goods once sold will not be taken back or exchanged except in case of manufacturing defects.</p>
            <p>3. All disputes are subject to jurisdiction of Tirunelveli only.</p>
            <p>4. Payment should be made in full before delivery.</p>
            <p>5. Replacement orders are subject to verification and approval.</p>
            <p>6. For any queries, contact our customer support at {{ $company['phone'] }} or {{ $company['email'] }}
            </p>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>{{ $company['name'] }} | {{ $company['address'] }} | Phone: {{ $company['phone'] }} | Email:
                {{ $company['email'] }}</p>
            @if ($order->is_replacement_order)
                <p style="color: #ff9900; font-weight: bold;">This is a replacement order - No payment required</p>
            @endif
        </div>
    </div>
</body>

</html>
