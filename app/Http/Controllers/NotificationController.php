<?php

namespace App\Http\Controllers;

use App\Models\PushNotification;
use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
    public function index()
    {
        $message = PushNotification::orderBy('id', 'desc')->paginate(10);
        return view('backend.notification', compact('message'));
    }

    public function show($id)
    {
        try {
            $notification = PushNotification::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->content,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not load notification details'
            ], 500);
        }
    }

    public function sendNotification($token, $message, $title)
    {

        $projectId = env('FIREBASE_PROJECT_ID');
        $url = "https://fcm.googleapis.com/v1/projects/".$projectId."/messages:send";

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return response()->json(['error' => 'Failed to get access token'], 500);
        }
        $body = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $message
                ],
                "data" => [
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    "type" => $data['type'] ?? 'general',
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, $body);
        return response()->json([
            'status' => $response->status(),
            'msessage' => "Successfully Send",
        ]);
    }

    private function getAccessToken()
    {
        $credentialsPath = storage_path(env('FIREBASE_CREDENTIALS'));

        if (!file_exists($credentialsPath)) {
            return null;
        }

        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

            $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);
            $accessToken = $credentials->fetchAuthToken();
            return $accessToken['access_token'] ?? null;
        try {

        } catch (\Exception $e) {
            return null;
        }
    }
    public function store(Request $request)
    {
        PushNotification::create(['title'=>$request->title, 'content'=>$request->message]);
        $tokens = User::whereNotNull('fcm_token')
            ->distinct()
            ->pluck('fcm_token')
            ->all();
        $message = $request->message;
        $title = $request->title;
        $this->sendBatchNotifications($tokens, $message, $title);

        return redirect('/push-notification')->with('status', 'Push Notification successfully send!');
    }

    public function resend($id)
    {
        try {
            $pushNotification = PushNotification::findOrFail($id);

            $tokens = User::whereNotNull('fcm_token')
                ->distinct()
                ->pluck('fcm_token')
                ->all();
            $message = $pushNotification->message;
            $title = $pushNotification->title;

            $this->sendBatchNotifications($tokens, $message, $title);

            return response()->json([
                'success' => true,
                'message' => 'Push Notification successfully resent!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend notification: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        $pushNotification = PushNotification::findOrFail($id);

        $pushNotification->delete();

        return redirect('/push-notification')->with('status', 'Push Notification successfully send!');

    }

    public function sendBatchNotifications($tokens, $message, $title)
    {
        $projectId = env('FIREBASE_PROJECT_ID');
        $url = "https://fcm.googleapis.com/v1/projects/".$projectId."/messages:send";

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return response()->json(['error' => 'Failed to get access token'], 500);
        }

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];

        $successCount = 0;
        $errorCount = 0;

        $tokens = array_unique($tokens);

        $batchSize = 500;
        $tokenBatches = array_chunk($tokens, $batchSize);

        foreach ($tokenBatches as $batchIndex => $tokenBatch) {
            $multiHandle = curl_multi_init();
            $curlHandles = [];

            foreach ($tokenBatch as $token) {
                $body = [
                    "message" => [
                        "token" => $token,
                        "notification" => [
                            "title" => $title,
                            "body" => $message
                        ],
                        "data" => [
                            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                            "type" => 'home',
                        ]
                    ]
                ];

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS => json_encode($body),
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                ]);

                curl_multi_add_handle($multiHandle, $ch);
                $curlHandles[] = $ch;
            }

            $running = null;
            do {
                $status = curl_multi_exec($multiHandle, $running);
            } while ($running && $status == CURLM_OK);

            foreach ($curlHandles as $ch) {
                $response = curl_multi_getcontent($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpCode >= 200 && $httpCode < 300) {
                    $successCount++;
                } else {
                    $errorCount++;
                    \Log::warning('FCM send failed', [
                        'code' => $httpCode,
                        'response' => $response
                    ]);
                }

                curl_multi_remove_handle($multiHandle, $ch);
                curl_close($ch);
            }

            curl_multi_close($multiHandle);

            if ($batchIndex < count($tokenBatches) - 1) {
                usleep(100000);
            }
        }

        return response()->json([
            'success_count' => $successCount,
            'error_count' => $errorCount,
        ]);
    }

}
