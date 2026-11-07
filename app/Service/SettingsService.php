<?php

namespace App\Service;

use App\Enums\UserType;
use App\Mail\UserDeleteMail;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SettingsService
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function profile(Request $request)
    {
        $data = User::where('id', auth()->user()->id)->where('status', true)->first();
        return view('backend.my_profile', compact('data'));
    }

    public function profileEdit(Request $request)
    {
        $data = User::where('id', auth()->user()->id)->where('status', true)->first();
        return view('backend.profile_edit', compact('data'));
    }

    public function updateProfile(array $data)
    {
        DB::beginTransaction();
        $user = User::findOrFail(auth()->id());
        $user->fill($data);
        $user->save();
        DB::commit();
        session()->flash("success", "Profile has been successfully updated!");
        return redirect('/admin/profile');
    }

    public function password(Request $request)
    {
        $data = User::where('id', auth()->user()->id)->where('status', true)->first();
        return view('backend.my_password', compact('data'));
    }

    public function updatePassword(array $data, User $user)
    {
        $currentPassword = auth()->user()->password;
        $newpassword = $data['new_password'];
        $oldpassword = $data['old_password'];
        $confirmpassword = $data['confirm_password'];
        if ($data['new_password'] != null) {
            if ($oldpassword !== null ||  $newpassword !== null || $confirmpassword !== null) {
                if (Hash::check($oldpassword, $currentPassword)) {
                    if ($newpassword == $confirmpassword) {
                        $data['password'] = Hash::make($data['new_password']);
                        $user = User::where('id', auth()->user()->id)->where('status', true)->first();
                        $user->fill($data);
                        $user->save();
                    } else {
                        session()->flash("error", "New Password and Confirm Password should be same!");
                        if ($user->type == UserType::SUPERADMIN) {
                            return redirect('/admin/profile');
                        } elseif ($user->type == UserType::SHOP) {
                            return redirect('/shop-admin/profile');
                        }
                    }
                } else {
                    session()->flash("error", "Old Password do not Match!");
                    if ($user->type == UserType::SUPERADMIN) {
                        return redirect('/admin/profile');
                    } elseif ($user->type == UserType::SHOP) {
                        return redirect('/shop-admin/profile');
                    }
                }
            }
        }
        session()->flash("success", "Password has been Successfully Updated!");
        if ($user->type == UserType::SUPERADMIN) {
            return redirect('/admin/profile');
        } elseif ($user->type == UserType::SHOP) {
            return redirect('/shop-admin/profile');
        }
    }

    /**
     * Dynamically configure Laravel's mailer from saved database settings.
     */
    public static function configureMailer(): void
    {
        try {
            $settings = app(self::class)->settingsIndex();
            if (!empty($settings['mail_host']) && !empty($settings['mail_username'])) {
                config([
                    'mail.default' => $settings['mail_mailer'] ?: 'smtp',
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.host' => $settings['mail_host'],
                    'mail.mailers.smtp.port' => (int)($settings['mail_port'] ?: 587),
                    'mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?: 'tls',
                    'mail.mailers.smtp.username' => $settings['mail_username'],
                    'mail.mailers.smtp.password' => $settings['mail_password'],
                    'mail.from.address' => $settings['mail_from_address'] ?: $settings['mail_username'],
                    'mail.from.name' => $settings['mail_from_name'] ?: ($settings['residence_name'] ?? 'SRM Residence'),
                ]);
            }
        } catch (\Throwable $e) {
            // Graceful fallback if database is not reachable yet
        }
    }

    public function settingsIndex()
    {
        $defaults = [
            // General Settings
            'residence_name' => 'SRM Residence',
            'residence_tagline' => 'Executive PG & Co-Living Residence',
            'contact_person' => 'Property Manager',
            'contact_designation' => 'Resident Warden',
            'contact_phone' => '+91 98765 43210',
            'contact_whatsapp' => '+91 98765 43210',
            'contact_email' => 'contact@srmresidence.com',
            'emergency_phone' => '+91 98765 00000',
            'contact_address' => 'SRM Residence, Main Road, Potheri, Kattankulathur, Tamil Nadu 603203',
            'google_maps_url' => '',
            'gate_closing_time' => '22:30',
            'office_hours' => '09:00 AM - 08:00 PM',
            'residence_stamp' => '',

            // Billing & Financial Defaults
            'default_eb_rate' => '10.00',
            'default_maintenance' => '0.00',
            'default_other_charges' => '0.00',
            'default_security_deposit' => '10000.00',
            'default_rent_type' => 'custom', // custom or fixed
            'bill_generation_day' => '1',
            'bill_due_day' => '5',
            'grace_period_days' => '3',
            'late_fee_type' => 'fixed', // fixed or per_day
            'late_fee_amount' => '100.00',
            'currency_symbol' => '₹',

            // Integrations - Razorpay
            'razorpay_enabled' => '0',
            'razorpay_mode' => 'test',
            'razorpay_key_id' => '',
            'razorpay_key_secret' => '',
            'razorpay_webhook_secret' => '',
            'razorpay_auto_reconcile' => '1',

            // Integrations - UPI & Direct Payment
            'upi_enabled' => '1',
            'upi_id' => 'srmresidence@okhdfcbank',
            'upi_merchant_name' => 'SRM RESIDENCE',
            'upi_qr_code' => '',
            'bank_name' => 'HDFC Bank',
            'bank_account_name' => 'SRM RESIDENCE',
            'bank_account_number' => '',
            'bank_ifsc_code' => '',
            'bank_branch' => 'Potheri Branch',

            // Integrations - Messaging (SMS / WhatsApp)
            'messaging_enabled' => '0',
            'messaging_provider' => 'whatsapp_cloud', // whatsapp_cloud, msg91, twilio
            'messaging_api_key' => '',
            'messaging_sender_id' => 'SRMRES',
            'whatsapp_phone_number_id' => '',
            'notify_bill_generated' => '1',
            'notify_payment_received' => '1',
            'notify_due_reminder' => '1',
            'notify_overdue_alert' => '1',

            // Integrations - SMTP / Mail
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'notifications@srmresidence.com',
            'mail_from_name' => 'SRM Residence',

            // Notifications & Alerts
            'admin_alert_email' => 'admin@srmresidence.com',
            'reminder_days_before_due' => '3',
            'bill_sms_template' => 'Dear {resident_name}, your room rent bill for {month} of Rs.{amount} has been generated. Due date: {due_date}. - SRM Residence',
            'payment_receipt_template' => 'Dear {resident_name}, we received your payment of Rs.{amount} on {payment_date}. Receipt #{receipt_no}. Thank you! - SRM Residence',

            // Policies & House Rules
            'house_rules' => "1. Visitors are allowed only in the common lounge until 8:00 PM.\n2. Main gates close promptly at 10:30 PM.\n3. Keep room and common areas clean.\n4. Loud music and disturbance to other residents is strictly prohibited.\n5. Smoking and alcohol consumption inside premises is strictly not permitted.",
            'terms_and_condition' => "1. Monthly rent must be cleared by the 5th of each month.\n2. Security deposit is refundable upon clearance of all utility dues at checkout.\n3. Minimum stay duration is 3 months.\n4. Electrical appliances other than laptops/chargers must be declared.",
            'notice_period_days' => '30',
            'notice_period_policy' => 'Residents must provide at least 30 days prior written notice before vacating their room. Vacating without notice may forfeit part of the security deposit.',
            'refund_policy' => 'Security deposit will be processed within 7 business days of checkout after room inspection and electricity meter reconciliation.',
        ];

        // 1. Fetch from settings table
        $dbSettings = \App\Models\Setting::getAllAsArray();

        // 2. Fetch from applications table (legacy)
        $appSettings = [];
        $apps = Application::all();
        foreach ($apps as $app) {
            if ($app->value !== null && $app->value !== '') {
                $appSettings[$app->name] = $app->value;
            }
        }

        // Merge defaults < legacy applications < new settings table
        return array_merge($defaults, $appSettings, $dbSettings);
    }

    public function saveSettings(array $data, Request $request)
    {
        $section = $data['section'] ?? 'general';

        // Handle file uploads (such as UPI QR Code)
        if ($request->hasFile('upi_qr_code') && $request->file('upi_qr_code')->isValid()) {
            $qrFile = $request->file('upi_qr_code');
            $filename = 'upi_qr_' . time() . '.' . $qrFile->getClientOriginalExtension();
            \Illuminate\Support\Facades\Storage::disk('public')->putFileAs('settings', $qrFile, $filename);
            
            // Auto crop flyer/standee screenshots so only the clean square QR code is stored
            $storedFullPath = \Illuminate\Support\Facades\Storage::disk('public')->path('settings/' . $filename);
            $this->autoCropQrImage($storedFullPath);

            \App\Models\Setting::set('upi_qr_code', 'settings/' . $filename, 'UPI QR Code Image');
        } elseif (isset($data['remove_upi_qr_code']) && $data['remove_upi_qr_code'] == '1') {
            \App\Models\Setting::set('upi_qr_code', '', 'UPI QR Code Image');
        }

        // Handle Residence Official Stamp upload
        if ($request->hasFile('residence_stamp') && $request->file('residence_stamp')->isValid()) {
            $stampFile = $request->file('residence_stamp');
            $filename = 'stamp_' . time() . '.' . $stampFile->getClientOriginalExtension();
            \Illuminate\Support\Facades\Storage::disk('public')->putFileAs('settings', $stampFile, $filename);
            \App\Models\Setting::set('residence_stamp', 'settings/' . $filename, 'Residence Official Stamp');
        } elseif (isset($data['remove_residence_stamp']) && $data['remove_residence_stamp'] == '1') {
            \App\Models\Setting::set('residence_stamp', '', 'Residence Official Stamp');
        }

        // Toggle checkboxes: if unchecked in HTML forms, ensure they are stored as 0
        $booleanKeys = [
            'integrations' => [
                'razorpay_enabled', 'razorpay_auto_reconcile',
                'upi_enabled',
                'messaging_enabled',
                'notify_bill_generated', 'notify_payment_received', 'notify_due_reminder', 'notify_overdue_alert'
            ],
            'billing' => [],
            'general' => [],
            'notifications' => [
                'notify_bill_generated', 'notify_payment_received', 'notify_due_reminder', 'notify_overdue_alert'
            ],
            'policies' => [],
        ];

        if (isset($booleanKeys[$section])) {
            foreach ($booleanKeys[$section] as $bKey) {
                if (!isset($data[$bKey])) {
                    $data[$bKey] = '0';
                }
            }
        }

        foreach ($data as $key => $value) {
            if (in_array($key, ['_token', '_method', 'section', 'upi_qr_code', 'remove_upi_qr_code', 'residence_stamp', 'remove_residence_stamp'])) {
                continue;
            }

            \App\Models\Setting::set($key, $value);

            // Backward compatibility sync with Application table
            if (in_array($key, ['terms_and_condition', 'refund_policy', 'contact_phone', 'contact_email', 'contact_address'])) {
                $group = 'General';
                if ($key === 'terms_and_condition') $group = 'Terms and Conditions';
                elseif ($key === 'refund_policy') $group = 'Refund Policy';
                elseif (str_starts_with($key, 'contact_')) $group = 'Contact';

                Application::updateOrCreate(
                    ['name' => $key],
                    ['value' => is_string($value) ? substr($value, 0, 255) : '', 'group' => $group]
                );
            }
        }

        $sectionLabels = [
            'general' => 'General & Property Settings',
            'billing' => 'Billing & Rent Defaults',
            'integrations' => 'Integrations (Payment Gateway, UPI, WhatsApp/SMS, SMTP)',
            'notifications' => 'Notification Templates & Alerts',
            'policies' => 'House Rules & Policies',
        ];
        $label = $sectionLabels[$section] ?? 'Settings';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$label} updated successfully!",
                'section' => $section,
            ]);
        }

        session()->flash('success', "{$label} updated successfully!");
        return redirect('/admin/settings?tab=' . $section);
    }

    public function termsConditions(array $data)
    {
        $terms = $data;
        foreach ($terms as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Terms and Conditions"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Terms and Conditions"]);
                }
            }
        }
        session()->flash("success", "Terms and Conditions has been Successfully Updated!");
        return redirect('/admin/settings');
    }

    public function refundPolicy(array $data)
    {
        foreach ($data as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Refund Policy"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Refund Policy"]);
                }
            }
        }
        session()->flash("success", "Return & Refund Policy has been Successfully Updated!");
        return redirect('/admin/settings');
    }

    public function contact(array $data)
    {
        $contact = $data;
        foreach ($contact as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Contact"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Contact"]);
                }
            }
        }
        session()->flash("success", "Contact has been Successfully Updated!");
        return redirect('/admin/settings');
    }
    public function customer(array $data)
    {
        $customer = $data;
        foreach ($customer as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Customer"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Customer"]);
                }
            }
        }
        session()->flash("success", "Customer has been Successfully Updated!");
        return redirect('/admin/settings');
    }
    public function commission(array $data)
    {
        $commission = $data;
        foreach ($commission as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Commission"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Commission"]);
                }
            }
        }
        session()->flash("success", "Commission has been Successfully Updated!");
        return redirect('/admin/settings');
    }
    public function charges(array $data)
    {
        $charges = $data;
        foreach ($charges as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Booking Charges"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Booking Charges"]);
                }
            }
        }
        session()->flash("success", "Booking Charges has been Successfully Updated!");
        return redirect('/admin/settings');
    }

    public function deliveryCharge(array $data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['_token', 'name']) || empty($value)) {
                continue;
            }

            Application::updateOrCreate(
                [
                    'group' => 'Delivery Charge',
                ],
                [
                    'name' => $key,
                    'value' => $value,
                ]
            );
        }

        session()->flash("success", "Delivery Charge has been Successfully Created!");
        return redirect('/admin/settings');
    }

    public function freeDeliveryAmountAbove(array $data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['_token', 'name']) || empty($value)) {
                continue;
            }

            Application::updateOrCreate(
                [
                    'group' => 'Free Delivery Amount Above',
                ],
                [
                    'name' => $key,
                    'value' => $value,
                ]
            );
        }

        session()->flash("success", "Free Delivery Amount Above has been Successfully Created!");
        return redirect('/admin/settings');
    }

    public function platformFee(array $data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['_token', 'name']) || empty($value)) {
                continue;
            }

            Application::updateOrCreate(
                [
                    'group' => 'Platform Fee',
                ],
                [
                    'name' => $key,
                    'value' => $value,
                ]
            );
        }

        session()->flash("success", "Platform Fee has been Successfully Created!");
        return redirect('/admin/settings');
    }

    public function returnReplaceDaysSettings(array $data)
    {
        foreach ($data as $key => $value) {
            if (Application::where("name", $key)->count() == 0) {
                Application::create(['name' => $key, "value" => $value, "group" => "Return Replace Days"]);
            } else {
                Application::where("name", $key)->update(["value" => $value, "group" => "Return Replace Days"]);
            }
        }
        session()->flash("success", "Return & Replace days settings updated successfully!");
        return redirect('/admin/settings');
    }

    public function requestUserDelete($request)
    {
        $validated = $request->validate([
            'email' => 'required',
            'phone' => 'required',
            'username' => 'required',
        ],[
            'email.required' => 'Email is required.',
            'phone.required' => 'Phone number is required',
            'username' => 'User Name is required',
        ]);
        $mailData = [
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
            'reason' => $request->reason
        ];
        Mail::to(config('mail.from.address'))->send(new UserDeleteMail($mailData));

        session()->flash("success", "Thank you trying our app. We will remove your data. It usually takes around 2-3 days.");
        return redirect('/user-data-remove');
    }

    /**
     * Auto crop uploaded UPI QR codes (such as Google Pay / PhonePe / Paytm standees or flyers)
     * so that only the square QR code matrix with a clean margin is stored and displayed.
     */
    protected function autoCropQrImage(string $fullPath): void
    {
        if (!file_exists($fullPath) || !function_exists('imagecreatefromstring')) {
            return;
        }

        $info = @getimagesize($fullPath);
        if (!$info) {
            return;
        }

        $width = $info[0];
        $height = $info[1];
        $mime = $info['mime'] ?? '';

        if ($width < 50 || $height < 50) {
            return;
        }

        $aspect = $height / $width;
        $isPortraitStandee = ($aspect > 1.15);

        $img = null;
        switch ($mime) {
            case 'image/jpeg':
                $img = @imagecreatefromjpeg($fullPath);
                break;
            case 'image/png':
                $img = @imagecreatefrompng($fullPath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $img = @imagecreatefromwebp($fullPath);
                }
                break;
            default:
                break;
        }

        if (!$img) {
            return;
        }

        try {
            $cropX = 0;
            $cropY = 0;
            $cropSize = min($width, $height);

            if ($isPortraitStandee) {
                $step = max(1, (int)round($width / 400));

                $darkPerRow = [];
                for ($y = 0; $y < $height; $y += $step) {
                    $count = 0;
                    for ($x = 0; $x < $width; $x += $step) {
                        $rgb = imagecolorat($img, $x, $y);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;
                        $lum = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                        if ($lum < 90) {
                            $count++;
                        }
                    }
                    $darkPerRow[$y] = $count;
                }

                $testedXCount = (int)ceil($width / $step);
                $rowThreshold = (int)round($testedXCount * 0.12);

                $qrStartY = null;
                $qrEndY = null;
                foreach ($darkPerRow as $y => $count) {
                    if ($count >= $rowThreshold) {
                        if ($qrStartY === null) {
                            $qrStartY = $y;
                        }
                        $qrEndY = $y;
                    }
                }

                if ($qrStartY !== null && $qrEndY !== null && ($qrEndY - $qrStartY) > ($height * 0.15)) {
                    $darkPerCol = [];
                    for ($x = 0; $x < $width; $x += $step) {
                        $count = 0;
                        for ($y = $qrStartY; $y <= $qrEndY; $y += $step) {
                            $rgb = imagecolorat($img, $x, $y);
                            $r = ($rgb >> 16) & 0xFF;
                            $g = ($rgb >> 8) & 0xFF;
                            $b = $rgb & 0xFF;
                            $lum = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                            if ($lum < 90) {
                                $count++;
                            }
                        }
                        $darkPerCol[$x] = $count;
                    }

                    $testedYCount = (int)ceil(($qrEndY - $qrStartY) / $step);
                    $colThreshold = (int)round($testedYCount * 0.10);

                    $qrStartX = null;
                    $qrEndX = null;
                    foreach ($darkPerCol as $x => $count) {
                        if ($count >= $colThreshold) {
                            if ($qrStartX === null) {
                                $qrStartX = $x;
                            }
                            $qrEndX = $x;
                        }
                    }

                    if ($qrStartX !== null && $qrEndX !== null && ($qrEndX - $qrStartX) > ($width * 0.15)) {
                        $detectedW = $qrEndX - $qrStartX;
                        $detectedH = $qrEndY - $qrStartY;
                        $detectedSize = max($detectedW, $detectedH);

                        $margin = (int)round($detectedSize * 0.045);
                        $cropX = max(0, $qrStartX - $margin);
                        $cropY = max(0, $qrStartY - $margin);
                        $cropW = min($width - $cropX, $detectedSize + ($margin * 2));
                        $cropH = min($height - $cropY, $detectedSize + ($margin * 2));
                        $cropSize = min($cropW, $cropH);
                    } else {
                        $cropX = 0;
                        $cropY = (int)round(($height - $width) / 2);
                        $cropSize = $width;
                    }
                } else {
                    $cropX = 0;
                    $cropY = (int)round(($height - $width) / 2);
                    $cropSize = $width;
                }
            } else {
                if ($width > $height * 1.15) {
                    $cropX = (int)round(($width - $height) / 2);
                    $cropY = 0;
                    $cropSize = $height;
                } else {
                    imagedestroy($img);
                    return;
                }
            }

            $cropped = imagecreatetruecolor($cropSize, $cropSize);
            $white = imagecolorallocate($cropped, 255, 255, 255);
            imagefill($cropped, 0, 0, $white);

            imagecopyresampled($cropped, $img, 0, 0, $cropX, $cropY, $cropSize, $cropSize, $cropSize, $cropSize);

            if ($mime === 'image/jpeg') {
                imagejpeg($cropped, $fullPath, 95);
            } elseif ($mime === 'image/png') {
                imagepng($cropped, $fullPath, 9);
            } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
                imagewebp($cropped, $fullPath, 95);
            }

            imagedestroy($cropped);
            imagedestroy($img);
        } catch (\Throwable $e) {
            if (is_resource($img) || (is_object($img) && $img instanceof \GdImage)) {
                @imagedestroy($img);
            }
        }
    }
}
