<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $bill->bill_number }} - {{ $settings['residence_name'] ?? 'SRM Residence' }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Caveat:wght@600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        :root {
            --primary: #0a5858;
            --primary-light: #f0fdfa;
            --primary-dark: #064040;
            --dark: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --light-bg: #f8fafc;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --info: #2563eb;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background-color: #f1f5f9;
            color: var(--dark);
            font-size: 13px;
            line-height: 1.5;
            padding: 30px 15px;
        }

        /* Screen Toolbar */
        .no-print-bar {
            max-width: 900px;
            margin: 0 auto 20px auto;
            background: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .btn-primary-print {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary-print:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        .btn-secondary-back {
            background: #fff;
            border-color: #cbd5e1;
            color: #475569;
        }

        .btn-secondary-back:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        /* Main Invoice Sheet */
        .invoice-sheet {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
            position: relative;
        }

        /* Invoice Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 24px;
            margin-bottom: 24px;
        }

        .brand-logo-area {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-icon {
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .brand-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .brand-tagline {
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
        }

        .property-meta {
            margin-top: 8px;
            font-size: 11.5px;
            color: #475569;
            line-height: 1.4;
        }

        .invoice-title-block {
            text-align: right;
        }

        .invoice-main-heading {
            font-size: 26px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: 0.5px;
            line-height: 1;
            margin-bottom: 6px;
        }

        .invoice-number-badge {
            font-size: 14px;
            font-weight: 700;
            color: #334155;
            font-family: monospace;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
        }

        /* Status Stamp */
        .status-stamp {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .status-stamp.paid {
            background: #ecfdf5;
            color: var(--success);
            border: 2px solid var(--success);
        }

        .status-stamp.partially_paid {
            background: #eff6ff;
            color: var(--info);
            border: 2px solid var(--info);
        }

        .status-stamp.unpaid {
            background: #fffbeb;
            color: var(--warning);
            border: 2px solid var(--warning);
        }

        .status-stamp.overdue {
            background: #fef2f2;
            color: var(--danger);
            border: 2px solid var(--danger);
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            background: var(--light-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px 22px;
            margin-bottom: 24px;
        }

        .info-col-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--muted);
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .info-name {
            font-size: 16px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .info-line {
            font-size: 12.5px;
            color: #475569;
            margin-bottom: 3px;
        }

        .info-line strong {
            color: var(--dark);
        }

        /* Electricity Meter Card */
        .eb-card {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 12px 18px;
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
        }

        .eb-card i {
            color: #d97706;
            margin-right: 6px;
        }

        /* Items Table */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .table-items th {
            background: var(--primary);
            color: #fff;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-items td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }

        .table-items tr:nth-child(even) td {
            background: #fafafa;
        }

        .table-items td.text-end, .table-items th.text-end {
            text-align: right;
        }

        .item-title {
            font-weight: 700;
            color: var(--dark);
        }

        .item-desc {
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 2px;
        }

        /* Summary & Payment Grid */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        /* UPI & Bank Box */
        .pay-box {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
            background: #f8fafc;
        }

        .pay-box-title {
            font-weight: 700;
            font-size: 13px;
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pay-details-inner {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .qr-image-container {
            width: 110px;
            height: 110px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 4px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .qr-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
            display: block;
        }

        .pay-info-text {
            font-size: 12px;
            color: #334155;
            line-height: 1.5;
        }

        .vpa-badge {
            background: #ecfdf5;
            color: var(--success);
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 700;
            border: 1px solid #a7f3d0;
        }

        /* Financial Total Card */
        .summary-card {
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 11px 18px;
            font-size: 13px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .summary-row span {
            white-space: nowrap;
        }

        .summary-row.total-row {
            background: #f8fafc;
            font-weight: 700;
        }

        .summary-row.due-row {
            background: #fef2f2;
            color: var(--danger);
            font-size: 15px;
            font-weight: 800;
            border-bottom: none;
        }

        .summary-row.paid-row {
            color: var(--success);
            font-weight: 600;
        }

        /* Payments History Table */
        .payments-history-section {
            margin-top: 15px;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .payments-history-header {
            background: #f1f5f9;
            padding: 8px 16px;
            font-weight: 700;
            font-size: 12px;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-payments {
            width: 100%;
            border-collapse: collapse;
        }

        .table-payments td, .table-payments th {
            padding: 8px 14px;
            font-size: 12px;
            border-bottom: 1px solid var(--border);
        }

        /* Footer & Signatures */
        .invoice-footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px dashed var(--border);
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .terms-block {
            max-width: 60%;
            font-size: 11px;
            color: var(--muted);
            line-height: 1.4;
        }

        .signatory-block {
            position: relative;
            text-align: center;
            width: 190px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stamp-and-sign-area {
            position: relative;
            width: 100%;
            height: 75px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .official-stamp-mark {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
            opacity: 0.90;
            z-index: 1;
        }

        .hand-signature {
            font-family: 'Caveat', cursive, 'Brush Script MT', sans-serif;
            font-size: 21px;
            color: #0f172a;
            position: relative;
            z-index: 2;
            transform: rotate(-3deg);
            margin-bottom: 2px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

        .signatory-line {
            width: 100%;
            border-bottom: 1.5px solid #475569;
            margin-bottom: 6px;
            position: relative;
            z-index: 2;
        }

        .signatory-label {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 2;
        }

        /* Amount in Words */
        .words-block {
            background: #f8fafc;
            border-radius: 6px;
            padding: 8px 14px;
            margin-bottom: 20px;
            font-size: 12px;
            border-left: 3px solid var(--primary);
        }

        .table-items-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 20px;
        }

        /* ==================================================== */
        /* MOBILE VIEW RESPONSIVENESS (< 768px)                */
        /* ==================================================== */
        @media screen and (max-width: 768px) {
            body {
                padding: 10px 8px !important;
                background: #f1f5f9 !important;
            }

            .no-print-bar {
                flex-direction: column !important;
                gap: 10px !important;
                align-items: stretch !important;
                padding: 12px 14px !important;
                margin-bottom: 12px !important;
            }

            .no-print-bar > div {
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                gap: 8px !important;
            }

            .no-print-bar .btn-toolbar {
                width: 100% !important;
                justify-content: center !important;
            }

            .invoice-sheet {
                padding: 18px 14px !important;
                max-width: 100% !important;
                border-radius: 12px !important;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
            }

            .invoice-header {
                flex-direction: column !important;
                gap: 16px !important;
                align-items: flex-start !important;
                padding-bottom: 18px !important;
                margin-bottom: 18px !important;
            }

            .invoice-header > div:first-child {
                max-width: 100% !important;
                width: 100% !important;
            }

            .invoice-title-block {
                text-align: left !important;
                width: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 6px !important;
            }

            .invoice-main-heading {
                font-size: 22px !important;
                letter-spacing: 0 !important;
            }

            .info-grid {
                grid-template-columns: 1fr !important;
                gap: 14px !important;
                padding: 14px 12px !important;
                margin-bottom: 18px !important;
            }

            .info-grid > div:last-child,
            .info-col.text-right {
                text-align: left !important;
                border-top: 1px dashed var(--border);
                padding-top: 12px;
            }

            .table-items {
                min-width: 520px !important;
            }

            .bottom-grid {
                grid-template-columns: 1fr !important;
                gap: 16px !important;
                margin-bottom: 18px !important;
            }

            .pay-details-inner {
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
                gap: 12px !important;
            }

            .qr-image-container {
                width: 130px !important;
                height: 130px !important;
                margin: 0 auto !important;
            }

            .pay-info-text {
                text-align: center !important;
            }

            .invoice-footer {
                flex-direction: column !important;
                gap: 20px !important;
                align-items: center !important;
                text-align: center !important;
            }

            .terms-block {
                max-width: 100% !important;
                text-align: center !important;
            }

            .signatory-block {
                width: 100% !important;
                max-width: 220px !important;
                margin: 10px auto 0 auto !important;
            }
        }

        /* ==================================================== */
        /* PRINT SPECIFIC STYLES                                */
        /* ==================================================== */
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 12px !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .invoice-sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            @page {
                size: A4 portrait;
                margin: 12mm 14mm;
            }
        }
    </style>
</head>
<body>

    @php
        // Helper function for Amount in Words (Indian numbering format)
        function formatAmountInWords($amount) {
            $number = floor($amount);
            $words = [
                0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
                6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
                11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
                16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
                20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
                60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
            ];
            $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];

            if ($number == 0) return 'Rupees Zero Only';

            $numStr = (string)$number;
            $len = strlen($numStr);
            $str = [];
            $i = 0;

            while ($number > 0) {
                $divider = ($i == 2) ? 10 : 100;
                $rem = $number % $divider;
                $number = floor($number / $divider);
                $i += ($divider == 10) ? 1 : 2;

                if ($rem) {
                    $counter = count($str);
                    $unit = $digits[$counter] ?? '';
                    $hundred = ($counter == 1 && !empty($str[0])) ? ' and ' : '';
                    if ($rem < 21) {
                        $str[] = $words[$rem] . ($unit ? ' ' . $unit : '') . $hundred;
                    } else {
                        $str[] = $words[floor($rem / 10) * 10] . ' ' . $words[$rem % 10] . ($unit ? ' ' . $unit : '') . $hundred;
                    }
                } else {
                    $str[] = '';
                }
            }

            $result = implode('', array_reverse($str));
            return 'Rupees ' . trim(preg_replace('/\s+/', ' ', $result)) . ' Only';
        }
    @endphp

    <!-- Screen Navigation Bar (Hidden when printed) -->
    <div class="no-print-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ url('/admin/billing') }}" class="btn-toolbar btn-secondary-back">
                <i class="fas fa-arrow-left"></i> Back to Billing
            </a>
            <span style="font-weight: 700; color: #475569; font-size: 13px;">
                Previewing Invoice: <span style="font-family: monospace; color: #0a5858;">{{ $bill->bill_number }}</span>
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="btn-toolbar btn-primary-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Invoice (A4)
            </button>
        </div>
    </div>

    <!-- Printable Invoice Sheet -->
    <div class="invoice-sheet">

        <!-- Header -->
        <div class="invoice-header">
            <div style="max-width: 55%;">
                <div class="brand-logo-area">
                    <div class="brand-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <div class="brand-title">{{ $settings['residence_name'] ?? 'SRM RESIDENCE' }}</div>
                        <div class="brand-tagline">{{ $settings['residence_tagline'] ?? 'Luxury Co-Living & Executive PG' }}</div>
                    </div>
                </div>
                <div class="property-meta">
                    <div><i class="fas fa-map-marker-alt" style="color: #0a5858; width: 14px;"></i> {{ $settings['contact_address'] ?? 'SRM Campus Road, Potheri, Kattankulathur, Tamil Nadu 603203' }}</div>
                    <div><i class="fas fa-phone-alt" style="color: #0a5858; width: 14px;"></i> {{ $settings['contact_phone'] ?? '+91 98765 43210' }} &nbsp;|&nbsp; <i class="fas fa-envelope" style="color: #0a5858; width: 14px;"></i> {{ $settings['contact_email'] ?? 'contact@srmresidence.com' }}</div>
                    @if(!empty($settings['contact_person']))
                        <div><i class="fas fa-user-tie" style="color: #0a5858; width: 14px;"></i> {{ $settings['contact_person'] }} ({{ $settings['contact_designation'] ?? 'Manager' }})</div>
                    @endif
                </div>
            </div>

            <div class="invoice-title-block">
                <div class="invoice-main-heading">RENT INVOICE</div>
                <div class="invoice-number-badge">{{ $bill->bill_number }}</div>
                <div>
                    @php
                        $stampCls = $bill->status;
                        $stampText = strtoupper(str_replace('_', ' ', $bill->status));
                    @endphp
                    <span class="status-stamp {{ $stampCls }}">
                        @if($bill->status === 'paid')
                            <i class="fas fa-check-circle me-1"></i>
                        @elseif($bill->status === 'partially_paid')
                            <i class="fas fa-adjust me-1"></i>
                        @elseif($bill->status === 'overdue')
                            <i class="fas fa-exclamation-circle me-1"></i>
                        @else
                            <i class="fas fa-clock me-1"></i>
                        @endif
                        {{ $stampText }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Billed To & Invoice Meta Grid -->
        <div class="info-grid">
            <div>
                <div class="info-col-title">Resident / Billed To</div>
                <div class="info-name">{{ $bill->tenant->name ?? 'Guest Resident' }}</div>
                <div class="info-line">
                    <strong>Room:</strong> 
                    @if($bill->room)
                        Room {{ $bill->room->room_number }} (Floor: {{ $bill->room->floor }}, {{ $bill->room->room_type }})
                    @elseif($bill->tenant)
                        {{ $bill->tenant->room_numbers_display }}
                    @else
                        Standard Unit
                    @endif
                </div>
                <div class="info-line"><strong>Mobile:</strong> {{ $bill->tenant->phone ?? '-' }}</div>
                <div class="info-line"><strong>Email:</strong> {{ $bill->tenant->email ?? '-' }}</div>
            </div>

            <div style="text-align: right;">
                <div class="info-col-title">Invoice Details</div>
                <div class="info-line"><strong>Billing Period:</strong> {{ $bill->billing_month }}</div>
                <div class="info-line"><strong>Invoice Date:</strong> {{ $bill->bill_date ? $bill->bill_date->format('d M, Y') : now()->format('d M, Y') }}</div>
                <div class="info-line"><strong>Due Date:</strong> <span style="color: #dc2626; font-weight: 700;">{{ $bill->due_date ? $bill->due_date->format('d M, Y') : '-' }}</span></div>
                <div class="info-line"><strong>Invoice Type:</strong> {{ $bill->bill_type }}</div>
            </div>
        </div>

        <!-- Electricity (EB) Meter Information (if applicable) -->
        @if($bill->eb_consumed_units !== null && (float)$bill->eb_consumed_units > 0)
            <div class="eb-card">
                <div>
                    <i class="fas fa-bolt"></i> <strong>Electricity Meter Reading:</strong> 
                    Previous Reading: <strong>{{ number_format($bill->eb_previous_reading, 2) }}</strong> &nbsp;|&nbsp; 
                    Current Reading: <strong>{{ number_format($bill->eb_current_reading, 2) }}</strong>
                </div>
                <div>
                    Units Consumed: <strong>{{ number_format($bill->eb_consumed_units, 2) }} units</strong> 
                    @ ₹{{ number_format($bill->eb_unit_rate, 2) }}/unit = 
                    <strong style="color: #059669; font-size: 13.5px;">₹{{ number_format($bill->eb_amount, 2) }}</strong>
                </div>
            </div>
        @endif

        <!-- Invoice Line Items Table -->
        <div class="table-items-wrapper">
        <table class="table-items">
            <thead>
                <tr>
                    <th style="width: 6%;">#</th>
                    <th style="width: 52%;">Description / Charge Item</th>
                    <th style="width: 22%;">Billing Period</th>
                    <th style="width: 20%;" class="text-end">Amount (INR)</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($bill->items) && is_array($bill->items) && count($bill->items) > 0)
                    @foreach($bill->items as $idx => $item)
                        <tr>
                            <td style="color: #64748b; font-weight: 600;">{{ $idx + 1 }}</td>
                            <td>
                                <div class="item-title">{{ $item['title'] ?? ($item['name'] ?? 'Charge Item') }}</div>
                                @if(!empty($item['details']))
                                    <div class="item-desc">{{ $item['details'] }}</div>
                                @endif
                            </td>
                            <td>{{ $bill->billing_month }}</td>
                            <td class="text-end" style="font-weight: 700;">₹{{ number_format((float)($item['amount'] ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="color: #64748b; font-weight: 600;">1</td>
                        <td>
                            <div class="item-title">{{ $bill->bill_type }}</div>
                            <div class="item-desc">{{ $bill->notes ?: 'Room accommodation and resident upkeep services' }}</div>
                        </td>
                        <td>{{ $bill->billing_month }}</td>
                        <td class="text-end" style="font-weight: 700;">₹{{ number_format((float)$bill->amount, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        </div>

        <!-- Amount in Words -->
        <div class="words-block">
            <strong>Amount in Words:</strong> {{ formatAmountInWords($bill->amount) }}
        </div>

        <!-- Bottom Grid: Direct UPI / QR Pay & Financial Summary -->
        <div class="bottom-grid">
            <!-- Left: UPI & Payment Instructions -->
            <div>
                <div class="pay-box">
                    <div class="pay-box-title">
                        <i class="fas fa-qrcode"></i> Instant UPI & Direct Bank Transfer
                    </div>
                    <div class="pay-details-inner">
                        <div class="qr-image-container">
                            <img src="@include('backend.partials.upi_qr_base64')" alt="UPI QR Code">
                        </div>
                        <div class="pay-info-text">
                            <div><strong>UPI VPA ID:</strong> <span class="vpa-badge">{{ $settings['upi_id'] ?? 'srmresidence@okhdfcbank' }}</span></div>
                            <div><strong>Payee Name:</strong> {{ $settings['upi_merchant_name'] ?? 'SRM RESIDENCE' }}</div>
                            @if(!empty($settings['bank_account_number']))
                                <div style="margin-top: 4px; font-size: 11.5px; color: #64748b;">
                                    <strong>Bank:</strong> {{ $settings['bank_name'] ?? 'Bank Transfer' }} | 
                                    <strong>A/C:</strong> {{ $settings['bank_account_number'] }} | 
                                    <strong>IFSC:</strong> {{ $settings['bank_ifsc_code'] ?? '-' }}
                                </div>
                            @endif
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                <i class="fas fa-mobile-alt me-1"></i> Scan with Google Pay, PhonePe, Paytm or BHIM UPI
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Financial Summary Table -->
            <div>
                <div class="summary-card">
                    <div class="summary-row total-row">
                        <span>Total Invoiced Amount:</span>
                        <span>₹{{ number_format((float)$bill->amount, 2) }}</span>
                    </div>
                    <div class="summary-row paid-row">
                        <span><i class="fas fa-check-circle me-1"></i> Total Amount Paid:</span>
                        <span>- ₹{{ number_format((float)$bill->paid_amount, 2) }}</span>
                    </div>
                    <div class="summary-row due-row">
                        <span>Net Balance Due:</span>
                        <span>₹{{ number_format((float)$bill->due_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Transactions History (if any) -->
        @if($bill->payments && $bill->payments->isNotEmpty())
            <div class="payments-history-section">
                <div class="payments-history-header">
                    <i class="fas fa-history me-1"></i> Payment Receipt History
                </div>
                <table class="table-payments">
                    <thead>
                        <tr style="background: #fafafa; color: #64748b; font-size: 11px; text-transform: uppercase;">
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Transaction Ref</th>
                            <th style="text-align: right;">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bill->payments as $p)
                            <tr>
                                <td style="font-weight: 700; color: #0a5858;">{{ $p->payment_number }}</td>
                                <td>{{ $p->payment_date ? $p->payment_date->format('d M, Y') : '-' }}</td>
                                <td><span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 11px;">{{ $p->payment_method }}</span></td>
                                <td style="font-family: monospace; font-size: 11px;">{{ $p->transaction_id ?: '-' }}</td>
                                <td style="text-align: right; font-weight: 700; color: #059669;">+ ₹{{ number_format($p->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Footer Notes & Authorized Signatory -->
        <div class="invoice-footer">
            <div class="terms-block">
                <strong>Terms & Conditions:</strong><br>
                1. Monthly rent & utility dues must be cleared on or before the due date.<br>
                2. Payments made after the due date are subject to standard late fee penalty.<br>
                3. This invoice is computer-generated and serves as an official proof of billing.
            </div>

            <div class="signatory-block">
                <div class="stamp-and-sign-area">
                    <div class="official-stamp-mark">
                        @include('backend.partials.residence_stamp', ['width' => 110, 'height' => 110, 'rotation' => '-6deg'])
                    </div>
                    <div class="hand-signature">
                        {{ $settings['residence_name'] ?? 'SRM Residence' }}
                    </div>
                </div>
                <div class="signatory-line"></div>
                <div class="signatory-label">Authorized Signatory</div>
                <div style="font-size: 10px; color: #64748b;">{{ $settings['residence_name'] ?? 'SRM Residence' }}</div>
            </div>
        </div>

    </div>

</body>
</html>
