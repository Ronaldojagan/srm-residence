<?php

namespace App\Service\Api;

use App\Models\Application;

class CommonService
{

    public function termsConditions()
    {
        $termsData = Application::where('group', 'Terms and Conditions')->first();

        if (!$termsData) {
            return [
                'status' => 0,
                'data'   => [
                    'title'   => null,
                    'content' => null,
                ]
            ];
        }

        return [
            'status' => 1,
            'data'   => [
                'title'   => $termsData->group,
                'content' => strip_tags($termsData->value),
            ]
        ];
    }

    public function returnRefund()
    {
        $returnRefundPolicyData = Application::where('group', 'Refund Policy')->first();

        if (!$returnRefundPolicyData) {
            return [
                'status' => 0,
                'data'   => [
                    'title'   => null,
                    'content' => null,
                ]
            ];
        }

        return [
            'status' => 1,
            'data'   => [
                'title'   => $returnRefundPolicyData->group,
                'content' => strip_tags($returnRefundPolicyData->value),
            ]
        ];
    }

    public function contact()
    {
        $data = [
            'email' => null,
            'phone' => null,
            'whatsapp' => null,
            'address' => null
        ];

        $contact = Application::where('group', 'Contact')->get();

        if ($contact->isEmpty()) {
            return ['status' => 0, 'data' => $data];
        }

        if ($contact->isNotEmpty()) {
            foreach ($contact as $contactValue) {
                $data[$contactValue->name] = $contactValue->value;
            }
        }

        return ['status' => 1, 'data' => $data];
    }


    public function logout($request)
    {
        $request->user()->tokens()->delete();
        return ['status' => 1, 'message' => 'Successfully Logged Out'];
    }


    public function refresh($request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'token' => $request->user()->createToken('api')->plainTextToken, 'message' => "Success", 'status' => 1
        ]);
    }

    public function deleteAccount($request)
    {
        $user = auth()->user();
        $reason = $request->input('reason');

        if (empty($reason)) {
            return response()->json([
                'status' => 0,
                'message' => 'Reason is required'
            ], 400);
        }

        $user->delete_reason = $reason;
        $user->save();

        $user->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Account deleted successfully'
        ]);
    }


}
