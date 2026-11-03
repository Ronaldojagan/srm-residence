<?php

namespace App\Service\Api\Shop;

use App\Enums\Status;
use App\Enums\UserType;
use App\Models\BankDetails;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthService
{
    public function signIn(array $data)
    {
        try {
            if (!isset($data['otp']) && isset($data['phone']) && !isset($data['name'])) {
                $user = User::where('phone', $data['phone'])->where('type', UserType::DELIVERY)
                        ->whereNull('deleted_at')->first();

                if ($user) {
                    if ($user->type != UserType::DELIVERY) {
                        return [
                            'status' => 0,
                            'message' => 'Access denied'
                        ];
                    }
                    if ($user->status != Status::ACTIVE) {
                        return [
                            'status' => 0,
                            'message' => 'Your account is inactive. Please contact support.'
                        ];
                    }
                }

                $otpData = (new Otp)->generate($data['phone'], 'numeric', 4, 15);
                if ($otpData->status) {
                    $this->sendOtpSms($data['phone'], $otpData->token);
                }
                return [
                    'status' => 1,
                    'message' => 'OTP sent',
                    'otp' => $otpData
                ];
            }

            if (isset($data['phone']) && isset($data['otp']) && !isset($data['name'])) {
                if ((new Otp)->validate($data['phone'], $data['otp'])->status) {
                    $user = User::where('phone', $data['phone'])->where('type', UserType::DELIVERY)
                        ->whereNull('deleted_at')->first();

                    if ($user) {
                        if ($user->status != Status::ACTIVE) {
                            return [
                                'status' => 0,
                                'message' => 'Your account is inactive. Please contact support.'
                            ];
                        }
                        Auth::login($user);
                        return [
                            'status' => 1,
                            'message' => 'Login success',
                            'user_id' => $user->id,
                            'token' => $user->createToken('API Token')->plainTextToken
                        ];
                    }

                    return [
                        'status' => 1,
                        'message' => 'OTP valid',
                        'verified' => true
                    ];
                }

                return [
                    'status' => 0,
                    'message' => 'OTP wrong'
                ];
            }

            if (isset($data['phone']) && isset($data['otp']) && isset($data['name'])) {
                $validator = Validator::make($data, [
                    'phone' => [
                        'required',
                        'string',
                        Rule::unique('users', 'phone')
                            ->where('type', UserType::DELIVERY)
                            ->whereNull('deleted_at'),
                    ],
                    'name'        => 'required|string',
                    'otp'         => 'required|string',
                    'device_id'      => 'required|string',
                    'fcm_token'      => 'required|string',

                    'tax_type' => ['nullable', Rule::in(['gst', 'pan'])],

                    'gst_no' => [
                        'exclude_unless:tax_type,gst',
                        'required',
                        'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
                    ],

                    'pan_no' => [
                        'exclude_unless:tax_type,pan',
                        'required',
                        'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
                    ],

                ]);

                if ($validator->fails()) {
                    return [
                        'status' => 0,
                        'message' => $validator->errors()->first()
                    ];
                }

               try {
                    DB::beginTransaction();
                    $user = new User();
                    $user->fill([
                        'name' => $data['name'],
                        'email' => $data['email'] ?? null,
                        'phone' => $data['phone'],
                        'type' => UserType::DELIVERY,
                        'status' => Status::ACTIVE,
                        'device_id' => $data['device_id'],
                        'fcm_token' => $data['fcm_token'],
                        'tax_type'   => $data['tax_type'] ?? null,
                        'gst_no'     => $data['gst_no'] ?? null,
                        'pan_no'     => $data['pan_no'] ?? null,
                    ]);
                    $user->save();

                    if ($data['shop_image'] ?? null) {
                        $shopImage = $this->saveFile($data['shop_image'], 'shop');
                    }

                    if (isset($data['shop_name'])) {
                        Shop::create([
                            'user_id' => $user->id,
                            'name' => $data['shop_name'],
                            'image' => $shopImage,
                            'email' => $data['email'] ?? null,
                            'owner_name' => $data['owner_name'],
                            'owner_phone' => $data['owner_phone'],
                            'address' => $data['address'],
                            'latitude' => $data['latitude'],
                            'longitude' => $data['longitude'],
                            'commission_percentage' => $data['commission_percentage'],
                        ]);
                    }

                    if (isset($data['bank_name'])) {
                        BankDetails::create([
                            'user_id' => $user->id,
                            'bank_name' => $data['bank_name'],
                            'account_number' => $data['account_number'],
                            'ifsc_code' => $data['ifsc_code'],
                            'holder_name' => $data['holder_name'],
                            'branch' => $data['branch'],
                        ]);
                    }

                    DB::commit();

                    Auth::login($user);

                    return [
                        'status' => 1,
                        'message' => 'Registration successful',
                        'user_id' => $user->id,
                        'token' => $user->createToken('API Token')->plainTextToken
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return ['status' => 0, 'message' => 'Invalid request'];
        } catch (\Exception $e) {
            return ['status' => 0, 'message' => '' . $e->getMessage()];
        }
    }

    public function saveFile($file, $path)
    {
        $image = null;
        if ($file->isValid()) {
            $imageName = time() . '_' . $file->getClientOriginalName();
            Storage::disk('public')->putFileAs($path, $file, $imageName);
            return $imageName;
        }
        return $image;
    }

    protected function sendOtpSms($phone, $otp)
    {
        $message = $this->getOtpMessage($otp);

        Log::channel('message_channel')->info('Sending message', ['phone' => $phone, 'message' => $message]);

        $apiUrl = 'https://online.chennaisms.com/api/mt/SendSMS';
        $queryParams = [
            'user' => env('SMS_USER'),
            'password' => env('SMS_PASS'),
            'senderid' => env('SMS_SENDER'),
            'channel' => 'Trans',
            'DCS' => 0,
            'flashsms' => 0,
            'number' => '91' . $phone,
            'text' => $message,
            'route' => 6
        ];

        $fullUrl = $apiUrl . '?' . http_build_query($queryParams);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);

        if (curl_errno($curl)) {
            Log::channel('message_channel')->error('CURL Error: ' . $curlError);
            Log::channel('message_channel')->error('HTTP Code: ' . $httpCode);
        } else {
            Log::channel('message_channel')->info('SMS API Response: ' . $response);
            Log::channel('message_channel')->info('HTTP Code: ' . $httpCode);

            $responseData = json_decode($response, true);
            if (isset($responseData['status']) && strtolower($responseData['status']) !== 'success') {
                Log::channel('message_channel')->error('SMS Failed: ' . $response);
            }
        }

        curl_close($curl);
        Log::channel('message_channel')->info('Message sending process completed');
    }

    protected function getOtpMessage($otp)
    {
        return "Alokha : {$otp} is your OTP for mobile number verification. Valid for 15 min. Alokha. chennaiepc";
    }

}
