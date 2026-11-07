<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Invoice #{{ $bill->bill_number }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #1e293b; line-height: 1.6;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                
                <!-- Main Container -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0a5858; padding: 30px 35px; text-align: left; color: #ffffff;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: 0.5px; color: #ffffff;">
                                            {{ $settings['residence_name'] ?? 'SRM RESIDENCE' }}
                                        </h1>
                                        <div style="font-size: 12px; color: #ccfbf1; margin-top: 4px;">
                                            {{ $settings['residence_tagline'] ?? 'Executive PG & Co-Living Residence' }}
                                        </div>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <span style="background-color: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3); color: #ffffff; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                                            RENT INVOICE
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 35px 35px 25px 35px;">
                            
                            <h2 style="margin: 0 0 10px 0; font-size: 18px; color: #0f172a;">
                                Dear {{ $bill->tenant->name ?? 'Resident' }},
                            </h2>
                            <p style="margin: 0 0 24px 0; font-size: 14px; color: #475569;">
                                Your room rent invoice for <strong>{{ $bill->billing_month }}</strong> has been generated. Please find the invoice summary and direct payment details below.
                            </p>

                            <!-- Invoice Metadata Card -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
                                            <tr>
                                                <td style="padding-bottom: 8px; color: #64748b; width: 40%;">Invoice Number:</td>
                                                <td style="padding-bottom: 8px; font-weight: 700; color: #0a5858;">#{{ $bill->bill_number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 8px; color: #64748b;">Billing Period:</td>
                                                <td style="padding-bottom: 8px; font-weight: 600; color: #1e293b;">{{ $bill->billing_month }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 8px; color: #64748b;">Room Assigned:</td>
                                                <td style="padding-bottom: 8px; font-weight: 600; color: #1e293b;">
                                                    Room {{ $bill->room ? $bill->room->room_number : ($bill->tenant ? $bill->tenant->room_numbers_display : '-') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 8px; color: #64748b;">Invoice Date:</td>
                                                <td style="padding-bottom: 8px; color: #1e293b;">{{ $bill->bill_date ? $bill->bill_date->format('d M, Y') : now()->format('d M, Y') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #64748b;">Due Date:</td>
                                                <td style="font-weight: 700; color: #dc2626;">{{ $bill->due_date ? $bill->due_date->format('d M, Y') : '-' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Charges Breakdown Table -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-bottom: 24px; font-size: 13px;">
                                <thead>
                                    <tr style="background-color: #0a5858; color: #ffffff;">
                                        <th align="left" style="padding: 10px 14px; border-radius: 6px 0 0 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Charge Description</th>
                                        <th align="right" style="padding: 10px 14px; border-radius: 0 6px 0 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Amount (INR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 12px 14px; color: #1e293b;">
                                            <strong>Room Monthly Rent</strong>
                                            <div style="font-size: 11px; color: #64748b;">Base accommodation & resident amenities</div>
                                        </td>
                                        <td align="right" style="padding: 12px 14px; font-weight: 600; color: #1e293b;">₹{{ number_format((float)($bill->rent_amount ?? $bill->amount), 2) }}</td>
                                    </tr>
                                    @if((float)($bill->electricity_amount ?? 0) > 0)
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 10px 14px; color: #1e293b;">Electricity Consumption (EB)</td>
                                            <td align="right" style="padding: 10px 14px; color: #1e293b;">₹{{ number_format((float)$bill->electricity_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if((float)($bill->maintenance_amount ?? 0) > 0)
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 10px 14px; color: #1e293b;">Maintenance & Facilities</td>
                                            <td align="right" style="padding: 10px 14px; color: #1e293b;">₹{{ number_format((float)$bill->maintenance_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if((float)($bill->other_charges ?? 0) > 0)
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 10px 14px; color: #1e293b;">Other Charges / Services</td>
                                            <td align="right" style="padding: 10px 14px; color: #1e293b;">₹{{ number_format((float)$bill->other_charges, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if((float)($bill->late_fee ?? 0) > 0)
                                        <tr style="border-bottom: 1px solid #f1f5f9; background-color: #fef2f2;">
                                            <td style="padding: 10px 14px; color: #dc2626;">Late Fee Penalty</td>
                                            <td align="right" style="padding: 10px 14px; color: #dc2626; font-weight: 600;">+ ₹{{ number_format((float)$bill->late_fee, 2) }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                            <!-- Total Summary Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
                                            <tr>
                                                <td style="padding-bottom: 6px; color: #64748b;">Total Invoiced:</td>
                                                <td align="right" style="padding-bottom: 6px; font-weight: 700; color: #0f172a;">₹{{ number_format((float)$bill->amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 6px; color: #059669;">Amount Paid:</td>
                                                <td align="right" style="padding-bottom: 6px; font-weight: 700; color: #059669;">- ₹{{ number_format((float)$bill->paid_amount, 2) }}</td>
                                            </tr>
                                            <tr style="border-top: 1.5px solid #cbd5e1;">
                                                <td style="padding-top: 8px; font-size: 15px; font-weight: 800; color: #0f172a;">Balance Due:</td>
                                                <td align="right" style="padding-top: 8px; font-size: 18px; font-weight: 800; color: {{ (float)$bill->due_amount > 0 ? '#dc2626' : '#059669' }};">
                                                    ₹{{ number_format((float)$bill->due_amount, 2) }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Payment Instructions Box -->
                            <div style="background-color: #f0fdfa; border: 1.5px solid #99f6e4; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                                <div style="font-weight: 800; font-size: 13px; color: #0f766e; text-transform: uppercase; margin-bottom: 10px;">
                                    ★ Instant UPI & Bank Transfer Options
                                </div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size: 12.5px;">
                                    <tr>
                                        <td style="padding-bottom: 6px; color: #134e4a; width: 35%;"><strong>UPI ID (VPA):</strong></td>
                                        <td style="padding-bottom: 6px; font-family: monospace; font-weight: 700; color: #0f766e; background-color: #ccfbf1; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                                            {{ $settings['upi_id'] ?? 'srmresidence@okhdfcbank' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-bottom: 6px; color: #134e4a;"><strong>Payee Name:</strong></td>
                                        <td style="padding-bottom: 6px; color: #1e293b;">{{ $settings['upi_merchant_name'] ?? ($settings['residence_name'] ?? 'SRM RESIDENCE') }}</td>
                                    </tr>
                                    @if(!empty($settings['bank_account_number']))
                                        <tr>
                                            <td style="padding-bottom: 6px; color: #134e4a;"><strong>Bank Details:</strong></td>
                                            <td style="padding-bottom: 6px; color: #1e293b;">
                                                {{ $settings['bank_name'] ?? 'Bank Transfer' }} | A/C: <strong>{{ $settings['bank_account_number'] }}</strong> | IFSC: <strong>{{ $settings['bank_ifsc_code'] ?? '-' }}</strong>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                                <div style="font-size: 11px; color: #0f766e; margin-top: 8px;">
                                    📱 Scan or pay via Google Pay, PhonePe, Paytm, or BHIM UPI to clear your balance.
                                </div>
                            </div>


                        </td>
                    </tr>

                    <!-- Signatory & Stamp Section -->
                    <tr>
                        <td style="padding: 10px 35px 25px 35px; border-top: 1px dashed #e2e8f0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size: 11px; color: #64748b; line-height: 1.5; width: 65%;">
                                        <strong>Terms:</strong> Dues must be settled on or before the due date.<br>
                                        This email serves as an authorized electronic billing statement.
                                    </td>
                                    <td align="right" style="vertical-align: bottom;">
                                        <div style="text-align: center; width: 140px;">
                                            <div style="font-size: 14px; font-family: cursive; color: #0a5858; font-weight: bold; margin-bottom: 2px;">
                                                {{ $settings['residence_name'] ?? 'SRM Residence' }}
                                            </div>
                                            <div style="border-bottom: 1.5px solid #64748b; margin-bottom: 4px;"></div>
                                            <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; color: #0f172a; letter-spacing: 0.5px;">
                                                Authorized Signatory
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 35px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11.5px; color: #64748b;">
                            <div style="font-weight: 700; color: #334155; margin-bottom: 4px;">
                                {{ $settings['residence_name'] ?? 'SRM Residence' }}
                            </div>
                            <div>
                                {{ $settings['contact_address'] ?? 'Main Road, Potheri, Kattankulathur, Tamil Nadu 603203' }}
                            </div>
                            <div style="margin-top: 4px;">
                                Phone: {{ $settings['contact_phone'] ?? '+91 98765 43210' }} | Email: {{ $settings['contact_email'] ?? 'contact@srmresidence.com' }}
                            </div>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
