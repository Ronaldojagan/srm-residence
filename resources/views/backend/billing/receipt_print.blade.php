<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $payment->payment_number }} - {{ $settings['residence_name'] ?? 'SRM Residence' }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Caveat:wght@600;700&display=swap" rel="stylesheet">
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

        .btn-whatsapp-share {
            background: #25D366;
            color: #fff;
            border-color: #22bf5b;
        }

        .btn-whatsapp-share:hover {
            background: #1eb855;
            color: #fff;
        }

        .btn-share-native {
            background: #0284c7;
            color: #fff;
            border-color: #0369a1;
        }

        .btn-share-native:hover {
            background: #0369a1;
            color: #fff;
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

        .receipt-signatory-wrap {
            position: relative;
            text-align: center;
            width: 160px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .receipt-stamp-area {
            position: relative;
            width: 100%;
            height: 65px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .receipt-stamp-mark {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
            opacity: 0.90;
            z-index: 1;
        }

        .receipt-hand-signature {
            font-family: 'Caveat', cursive, 'Brush Script MT', sans-serif;
            font-size: 19px;
            color: #0f172a;
            position: relative;
            z-index: 2;
            transform: rotate(-3deg);
            margin-bottom: 2px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

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

            .no-print-bar .btn-toolbar {
                width: 100% !important;
                justify-content: center !important;
            }

            .receipt-sheet {
                padding: 20px 16px !important;
                max-width: 100% !important;
                border-radius: 12px !important;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
            }

            .receipt-row {
                font-size: 13px !important;
                padding: 8px 0 !important;
            }

            .amount-paid-box {
                padding: 14px !important;
            }

            .amount-paid-box .amt {
                font-size: 24px !important;
            }

            .receipt-footer {
                flex-direction: column !important;
                gap: 20px !important;
                align-items: center !important;
                text-align: center !important;
            }
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
    <script src="{{ asset('assets/libs/html2canvas.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jspdf.umd.min.js') }}"></script>
</head>
<body>

    <div class="no-print-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ url('/admin/billing') }}" class="btn-toolbar btn-secondary-back">
                <i class="fas fa-arrow-left"></i> Back to Billing
            </a>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn-toolbar btn-whatsapp-share" id="btnShareRcpPdf" onclick="shareReceiptFile('pdf')">
                <i class="fab fa-whatsapp me-1"></i><i class="fas fa-file-pdf me-1"></i> Share PDF
            </button>
            <button type="button" class="btn-toolbar btn-share-native" id="btnShareRcpImg" onclick="shareReceiptFile('image')">
                <i class="fab fa-whatsapp me-1"></i><i class="fas fa-image me-1"></i> Share Image
            </button>
            <button type="button" class="btn-toolbar btn-primary-print" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Receipt
            </button>
        </div>
    </div>

    <div class="receipt-sheet" id="receiptSheet">
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
            <div class="receipt-signatory-wrap">
                <div class="receipt-stamp-area">
                    <div class="receipt-stamp-mark">
                        @include('backend.partials.residence_stamp', ['width' => 95, 'height' => 95, 'rotation' => '-6deg'])
                    </div>
                    <div class="receipt-hand-signature">
                        {{ $settings['residence_name'] ?? 'SRM Residence' }}
                    </div>
                </div>
                <div style="width: 100%; border-bottom: 1.5px solid #475569; margin-bottom: 4px; position: relative; z-index: 2;"></div>
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #0f172a; position: relative; z-index: 2;">Authorized Signature</div>
                <div style="font-size: 9px; color: #64748b; position: relative; z-index: 2;">{{ $settings['residence_name'] ?? 'SRM Residence' }}</div>
            </div>
        </div>
    </div>

    <script>
        const receiptShareData = {
            title: 'SRM RESIDENCE - Payment Receipt {{ $payment->payment_number }}',
            resident: '{{ addslashes($payment->tenant->name ?? "Resident") }}',
            phone: '{{ $payment->tenant && $payment->tenant->phone ? preg_replace("/[^0-9]/", "", $payment->tenant->phone) : "" }}',
            room: '{{ addslashes($payment->bill && $payment->bill->room ? $payment->bill->room->room_number : ($payment->tenant ? $payment->tenant->room_numbers_display : "-")) }}',
            receiptNo: '{{ $payment->payment_number }}',
            invoiceNo: '{{ $payment->bill ? $payment->bill->bill_number : "-" }}',
            date: '{{ $payment->payment_date ? $payment->payment_date->format("d M, Y") : now()->format("d M, Y") }}',
            amount: '₹{{ number_format((float)$payment->amount, 2) }}',
            method: '{{ $payment->payment_method }}',
            txnId: '{{ $payment->transaction_id ?? "-" }}'
        };

        function getReceiptShareMessage(fileType) {
            const ext = (fileType || 'PDF').toUpperCase();
            return `🧾 *{{ addslashes($settings['residence_name'] ?? 'SRM RESIDENCE') }} - PAYMENT RECEIPT*\n` +
                   `━━━━━━━━━━━━━━━━━━━━━━━\n` +
                   `✅ *Payment Confirmed & Credited*\n` +
                   `📄 *Receipt No:* ${receiptShareData.receiptNo}\n` +
                   `👤 *Resident:* ${receiptShareData.resident}\n` +
                   `🚪 *Room:* ${receiptShareData.room}\n` +
                   `💰 *Amount Paid:* ${receiptShareData.amount}\n` +
                   `💳 *Payment Mode:* ${receiptShareData.method}\n` +
                   `📅 *Payment Date:* ${receiptShareData.date}\n` +
                   (receiptShareData.txnId !== '-' ? `🔢 *Ref / UTR:* ${receiptShareData.txnId}\n` : '') +
                   `📑 *Against Invoice:* ${receiptShareData.invoiceNo}\n` +
                   `━━━━━━━━━━━━━━━━━━━━━━━\n` +
                   `📑 *Attached: Official Payment Receipt (${ext})*`;
        }

        async function shareReceiptFile(type) {
            const sheet = document.getElementById('receiptSheet');
            if (!sheet) return;

            const btn = type === 'pdf' ? document.getElementById('btnShareRcpPdf') : document.getElementById('btnShareRcpImg');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> Generating ${type.toUpperCase()}...`;
            }

            try {
                const canvas = await html2canvas(sheet, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff'
                });

                let file = null;
                let fileName = '';

                if (type === 'pdf') {
                    fileName = `Receipt_${receiptShareData.receiptNo}.pdf`;
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a5');
                    const pageWidth = pdf.internal.pageSize.getWidth();
                    const pageHeight = (canvas.height * pageWidth) / canvas.width;
                    const imgData = canvas.toDataURL('image/jpeg', 0.95);
                    pdf.addImage(imgData, 'JPEG', 0, 0, pageWidth, pageHeight, undefined, 'FAST');
                    const blob = pdf.output('blob');
                    file = new File([blob], fileName, { type: 'application/pdf' });
                } else {
                    fileName = `Receipt_${receiptShareData.receiptNo}.png`;
                    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
                    file = new File([blob], fileName, { type: 'image/png' });
                }

                const msg = getReceiptShareMessage(type);

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        title: receiptShareData.title,
                        text: msg,
                        files: [file]
                    });
                } else {
                    const downloadLink = document.createElement('a');
                    downloadLink.href = URL.createObjectURL(file);
                    downloadLink.download = fileName;
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);

                    let waUrl = '';
                    const phone = receiptShareData.phone;
                    if (phone && phone.length >= 10) {
                        const targetPhone = phone.length === 10 ? '91' + phone : phone;
                        waUrl = `https://api.whatsapp.com/send?phone=${targetPhone}&text=${encodeURIComponent(msg)}`;
                    } else {
                        waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`;
                    }
                    window.open(waUrl, '_blank');
                    alert(`Receipt ${type.toUpperCase()} saved to your Downloads!\nPlease attach this file in the WhatsApp chat to complete sharing.`);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error('File share error:', err);
                    alert('Could not share file: ' + (err.message || err));
                }
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }
        }
    </script>
</body>
</html>
