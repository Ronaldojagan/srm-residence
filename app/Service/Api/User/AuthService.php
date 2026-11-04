<?php

namespace App\Service\Api\User;

use App\Enums\Status;
use App\Enums\UserStatusEnum;
use App\Enums\UserType;
use App\Models\Address;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthService
{
    public function signIn(array $data)
    {
        try {
            if (!isset($data['otp']) && isset($data['phone']) && !isset($data['lat'])) {

                $user = User::where('phone', $data['phone'])->where('type', UserType::USER)
                        ->whereNull('deleted_at')->first();

                if ($user) {
                    if ($user->type != UserType::USER) {
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
                // if ($otpData->status) {
                //     $this->sendOtpSms($data['phone'], $otpData->token);
                // }
                return [
                    'status' => 1,
                    'message' => 'OTP sent',
                    'otp' => $otpData
                ];
            }

            if (isset($data['phone']) && isset($data['otp']) && !isset($data['lat'])) {
                if ((new Otp)->validate($data['phone'], $data['otp'])->status) {
                    $user = User::where('phone', $data['phone'])->where('type', UserType::USER)
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

            if (isset($data['phone']) && isset($data['otp']) && isset($data['lat'])) {
                $validator = Validator::make($data, [
                    'phone' => [
                        'required',
                        'string',
                        Rule::unique('users', 'phone')
                            ->where('type', UserType::USER)
                            ->whereNull('deleted_at'),
                    ],
                    'lat'         => 'required|string',
                    'long'        => 'required|string',
                    'city'        => 'required|string',
                    'district'    => 'required|string',
                    'state'    => 'required|string',
                    'country'    => 'required|string',
                    'name'        => 'required|string',
                    'otp'         => 'required|string',
                    'pincode'     => 'required|string',
                    'flat_no'     => 'required|string',
                    'area_name'   => 'nullable|string',
                    'street'      => 'required|string',
                    // 'device_id'      => 'required|string',
                    // 'fcm_token'      => 'required|string',
                    'address_type'=> ['required', Rule::in(['home', 'office', 'other'])],
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
                        'phone' => $data['phone'],
                        'type' => UserType::USER,
                        'status' => Status::ACTIVE,
                        // 'device_id' => $data['device_id'],
                        // 'fcm_token' => $data['fcm_token'],
                    ]);
                    $user->save();
                    $this->storeAddress($user->id, $data);

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
        return "SRM RESIDENCE : {$otp} is your OTP for mobile number verification. Valid for 15 min. SRM RESIDENCE.";
    }

    protected function storeAddress($user_id, $data)
    {
        $address = new Address();
        $address->fill([
            'user_id' => $user_id,
            'flat_no' => $data['flat_no'] ?? null,
            'street' => $data['street'] ?? null,
            'area_name' => $data['area_name'] ?? null,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'city' => $data['city'],
            'district' => $data['district'],
            'pincode' => $data['pincode'],
            'lat' => $data['lat'],
            'long' => $data['long'],
            'address_type' => $data['address_type'],
            'is_default'=>true,
        ]);

        return $address->save();
    }
}
