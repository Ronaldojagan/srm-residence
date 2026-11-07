<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $payment->payment_number }} - {{ $settings['residence_name'] ?? 'SRM Residence' }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        :root {
            --primary: #0a5858;
            --primary-dark: #064040;
            --dark: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --success: #059669;
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
            padding: 30px 15px;
        }

        .no-print-bar {
            max-width: 650px;
            margin: 0 auto 20px auto;
            background: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
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

        .receipt-sheet {
            max-width: 650px;
            margin: 0 auto;
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed var(--border);
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .receipt-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
        }

        .receipt-badge {
            display: inline-block;
            background: #ecfdf5;
            color: var(--success);
            border: 1px solid #a7f3d0;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 6px;
        }

        .receipt-number {
            font-family: monospace;
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            margin-top: 6px;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        .receipt-row .label {
            color: var(--muted);
        }

        .receipt-row .val {
            font-weight: 600;
            color: var(--dark);
            text-align: right;
        }

        .amount-box {
            background: #ecfdf5;
            border: 2px solid #a7f3d0;
            border-radius: 10px;
            padding: 18px;
            text-align: center;
            margin: 24px 0;
        }

        .amount-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--success);
            letter-spacing: 0.5px;
        }

        .amount-val {
            font-size: 28px;
            font-weight: 800;
            color: var(--success);
            margin: 4px 0;
        }

        .receipt-footer {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .receipt-sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            @page {
                size: A5 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="{{ url('/admin/billing') }}" class="btn-toolbar btn-secondary-back">
            <i class="fas fa-arrow-left"></i> Back to Billing
        </a>
        <button type="button" class="btn-toolbar btn-primary-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print Receipt
        </button>
    </div>

    <div class="receipt-sheet">
        <div class="receipt-header">
            <div class="receipt-title">{{ $settings['residence_name'] ?? 'SRM RESIDENCE' }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                {{ $settings['contact_address'] ?? 'SRM Campus Road, Chennai, Tamil Nadu' }}
            </div>
            <div>
                <span class="receipt-badge">OFFICIAL PAYMENT ACKNOWLEDGMENT</span>
            </div>
            <div class="receipt-number">{{ $payment->payment_number }}</div>
        </div>

        <div>
            <div class="receipt-row">
                <span class="label">Payment Date:</span>
                <span class="val">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : now()->format('d M, Y') }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Received From (Resident):</span>
                <span class="val">{{ $payment->tenant->name ?? 'Resident' }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Room Number:</span>
                <span class="val">
                    Room {{ $payment->bill && $payment->bill->room ? $payment->bill->room->room_number : ($payment->tenant ? $payment->tenant->room_numbers_display : '-') }}
                </span>
            </div>
            <div class="receipt-row">
                <span class="label">Against Invoice:</span>
                <span class="val" style="color: #0a5858;">
                    {{ $payment->bill ? $payment->bill->bill_number : '-' }} 
                    ({{ $payment->bill ? $payment->bill->billing_month : '' }})
                </span>
            </div>
            <div class="receipt-row">
                <span class="label">Payment Mode:</span>
                <span class="val">{{ $payment->payment_method }}</span>
            </div>
            @if(!empty($payment->transaction_id))
                <div class="receipt-row">
                    <span class="label">Transaction Ref / UTR:</span>
                    <span class="val font-monospace">{{ $payment->transaction_id }}</span>
                </div>
            @endif
            @if(!empty($payment->receiver))
                <div class="receipt-row">
                    <span class="label">Collected By:</span>
                    <span class="val">{{ $payment->receiver->name }}</span>
                </div>
            @endif
        </div>

        <div class="amount-box">
            <div class="amount-label">Amount Received</div>
            <div class="amount-val">₹{{ number_format((float)$payment->amount, 2) }}</div>
            <div style="font-size: 11px; color: #059669; font-weight: 600;">Payment confirmed & credited successfully</div>
        </div>

        <div class="receipt-footer">
            <div style="font-size: 11px; color: #64748b; line-height: 1.4;">
                Thank you for your payment.<br>
                For any clarifications, please contact the warden office.
            </div>
            <div style="text-align: center; width: 140px;">
                <div style="height: 35px; border-bottom: 1px solid #64748b; margin-bottom: 4px;"></div>
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase;">Authorized Signature</div>
            </div>
        </div>
    </div>

</body>
</html>
