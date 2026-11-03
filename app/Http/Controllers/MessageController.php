<?php

namespace App\Http\Controllers;

class MessageController extends Controller
{

    public function messageTemplate($message, $phone, $template)
    {
        $apiKey = env('SMS_API_TOKEN');
        $senderId = env('SMS_SENDER_ID');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://app.mydreamstechnology.in/vb/apikey.php?apikey=" . $apiKey . "&senderid=" . $senderId . "&number=" . $phone . "&message=" . urlencode($message) . "&template=" . $template,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

}
