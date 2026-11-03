<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop Invoice - {{ $order->invoice_no }}</title>
    <style>
        @page {
            margin: 10mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 95%;
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            width: 100%;
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

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Warning box */
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        /* Section titles */
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #2c3e50;
        }

        /* Info boxes using float */
        .info-boxes {
            margin-bottom: 20px;
            overflow: hidden;
        }

        .info-box {
            float: left;
            width: 48%;
            padding: 15px;
            font-size: 9pt;
        }

        .info-box:first-child {
            margin-right: 4%;
        }

        .info-box h3 {
            font-size: 11pt;
            color: #2c3e50;
            margin: 0 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #2c3e50;
        }

        .info-box p {
            margin: 5px 0;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 80px;
        }

        /* Summary table */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-cell {
            width: 25%;
            padding: 15px 10px;
            background: white;
            border: 1px solid #ddd;
            border-top: 3px solid #2c3e50;
            text-align: center;
            vertical-align: top;
        }

        .card-title {
            color: #666;
            font-size: 8pt;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .card-value {
            font-size: 14pt;
            font-weight: bold;
            color: #2c3e50;
            margin: 8px 0;
        }

        .card-subtext {
            color: #888;
            font-size: 8pt;
            margin-top: 5px;
        }

        /* Products table - SIMPLE structure for DOMPDF */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }

        .products-table thead tr th {
            background-color: #2c3e50;
            color: white;
            padding: 10px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2c3e50;
        }

        .products-table tbody tr td {
            padding: 8px 5px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }

        .products-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .product-name {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .quantity-badge {
            display: inline-block;
            background: #e8f4ff;
            color: #2196F3;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: bold;
        }

        /* Totals - using simple div structure */
        .totals-wrapper {
            margin-bottom: 20px;
        }

        .totals-table {
            width: 350px;
            float: right;
            border: 1px solid #ddd;
            background: white;
            font-size: 10pt;
        }

        .total-row {
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
        }

        .total-row:last-child {
            border-bottom: none;
            background: #f0f2ff;
            font-weight: bold;
            font-size: 11pt;
            border-top: 2px solid #2c3e50;
        }

        .total-label {
            display: inline-block;
            width: 60%;
        }

        .total-value {
            display: inline-block;
            width: 38%;
            text-align: right;
            font-family: DejaVu Sans Mono, monospace;
        }

        /* Commission box */
        .commission-box {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin: 20px 0;
        }

        .commission-box h3 {
            color: #3498db;
            margin: 0 0 10px 0;
            font-size: 12pt;
        }

        .commission-row {
            padding: 6px 0;
            border-bottom: 1px solid #ddd;
            font-size: 10pt;
        }

        .commission-row:last-child {
            border-bottom: none;
            border-top: 2px solid #3498db;
            padding-top: 10px;
            margin-top: 5px;
            font-weight: bold;
        }

        .comm-label {
            display: inline-block;
            width: 65%;
        }

        .comm-value {
            display: inline-block;
            width: 33%;
            text-align: right;
            font-family: DejaVu Sans Mono, monospace;
        }

        .net-payable {
            color: #27ae60;
            font-size: 12pt;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            margin-top: 30px;
            font-size: 9pt;
        }

        .footer-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .footer-disclaimer {
            font-size: 8pt;
            opacity: 0.9;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }

        /* Signature */
        .signature {
            text-align: right;
            margin: 30px 0 20px 0;
        }

        .signature-line {
            width: 200px;
            border-top: 1px solid #333;
            margin: 30px 0 5px auto;
        }

        /* Utility */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .mb-20 { margin-bottom: 20px; }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
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

        <!-- Summary Cards -->
        <h2 class="section-title">Order Summary</h2>
        <table class="summary-table">
            <tr>
                <td class="summary-cell">
                    <div class="card-title">Payment Status</div>
                    <div class="card-value" style="color: {{ $order->payment_status == 'paid' ? '#27ae60' : '#f39c12' }};">
                        {{ ucfirst($order->payment_status ?? 'pending') }}
                    </div>
                    <div class="card-subtext">{{ $order->payment_method ?? 'N/A' }}</div>
                </td>
                <td class="summary-cell">
                    <div class="card-title">Shop Items</div>
                    <div class="card-value">{{ $shopOrderItems->count() }}</div>
                    <div class="card-subtext">Products</div>
                </td>
                <td class="summary-cell">
                    <div class="card-title">Order Date</div>
                    <div class="card-value">{{ $order->created_at->format('d M') }}</div>
                    <div class="card-subtext">{{ $order->created_at->format('Y') }}</div>
                </td>
                <td class="summary-cell">
                    <div class="card-title">Shop Total</div>
                    <div class="card-value">₹{{ number_format($shopTotals['grand_total'], 2) }}</div>
                    <div class="card-subtext">Gross Amount</div>
                </td>
            </tr>
        </table>

        <!-- Party Details -->
        <div class="info-boxes clearfix">
            <!-- Seller -->
            <div class="info-box">
                <h3>SELLER ({{ $shop->name }})</h3>
                <p><span class="info-label">Business:</span> {{ $shop->business_name }}</p>
                <p><span class="info-label">Address:</span> {{ $shop->address }}, {{ $shop->city }}</p>
                <p><span class="info-label">Contact:</span> {{ $shop->phone }}</p>
                <p><span class="info-label">Email:</span> {{ $shop->email }}</p>
                @if($shop->gst_number)
                <p><span class="info-label">GSTIN:</span> {{ $shop->gst_number }}</p>
                @endif
                @if($shop->pan_number)
                <p><span class="info-label">PAN:</span> {{ $shop->pan_number }}</p>
                @endif
            </div>

            <!-- Buyer -->
            <div class="info-box">
                <h3>CUSTOMER</h3>
                <p><span class="info-label">Name:</span> <strong>{{ $order->user->name ?? 'N/A' }}</strong></p>
                <p><span class="info-label">Phone:</span> {{ $order->user->phone ?? 'N/A' }}</p>
                <p><span class="info-label">Email:</span> {{ $order->user->email ?? 'N/A' }}</p>
                <p><span class="info-label">Address:</span> {{ $order->shippingAddress->full_address }}</p>
                <p><span class="info-label">Shipping Phone:</span> {{ $order->shippingAddress->phone ?? $order->user->phone }}</p>
            </div>
        </div>

        <!-- Products Table -->
        <h2 class="section-title">Products from {{ $shop->name }}</h2>
        <table class="products-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="35%">Product</th>
                    <th width="15%">Category</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="17%" class="text-right">Unit Price</th>
                    <th width="18%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shopOrderItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="product-name">{{ $item->product_name }}</div>
                        @if($item->description)
                        <div style="color: #666; font-size: 8pt; margin-top: 2px;">
                            {{ Str::limit($item->description, 60) }}
                        </div>
                        @endif
                    </td>
                    <td>{{ $item->product->category->name ?? 'N/A' }}</td>
                    <td class="text-center">
                        <span class="quantity-badge">{{ $item->quantity }}</span>
                    </td>
                    <td class="text-right">₹{{ number_format($item->amount, 2) }}</td>
                    <td class="text-right"><strong>₹{{ number_format($item->total_amount, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- <div class="totals-wrapper clearfix">
            <div class="totals-table">
                <div class="total-row">
                    <span class="total-label">Subtotal (Your Items):</span>
                    <span class="total-value">₹{{ number_format($shopTotals['subtotal'], 2) }}</span>
                </div>

                @if($shopTotals['discount'] > 0)
                <div class="total-row">
                    <span class="total-label">Discount Applied:</span>
                    <span class="total-value" style="color: #27ae60;">- ₹{{ number_format($shopTotals['discount'], 2) }}</span>
                </div>
                @endif

                @if($shopTotals['delivery'] > 0)
                <div class="total-row">
                    <span class="total-label">Delivery Share:</span>
                    <span class="total-value">₹{{ number_format($shopTotals['delivery'], 2) }}</span>
                </div>
                @endif

                @if($shopTotals['platform_fee'] > 0)
                <div class="total-row">
                    <span class="total-label">Platform Fee:</span>
                    <span class="total-value">₹{{ number_format($shopTotals['platform_fee'], 2) }}</span>
                </div>
                @endif

                @if($shopTotals['tax'] > 0)
                <div class="total-row">
                    <span class="total-label">Tax (GST):</span>
                    <span class="total-value">₹{{ number_format($shopTotals['tax'], 2) }}</span>
                </div>
                @endif

                <div class="total-row">
                    <span class="total-label bold">Shop Gross Amount:</span>
                    <span class="total-value bold">₹{{ number_format($shopTotals['grand_total'], 2) }}</span>
                </div>
            </div>
        </div> --}}
    </div>
</body>
</html>
