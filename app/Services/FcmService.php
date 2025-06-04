<?php

namespace App\Services;

use Google\Client;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected $httpClient;
    protected $accessToken;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'verify' => base_path('storage/certs/cacert.pem') // ضع نسخة من cacert.pem في هذا المسار
        ]);

        $this->accessToken = $this->getAccessToken();
    }
    // بقية الكود كما هو...

    protected function getAccessToken()
    {
        $client = new Client();
        $client->setAuthConfig(base_path(env('FIREBASE_CREDENTIALS')));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $client->refreshTokenWithAssertion();

        return $client->getAccessToken()['access_token'];
    }

    public function sendNotificationToToken($fcmToken, $title, $body)
    {
        $projectId = $this->getProjectId();

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'android' => [
                    'priority' => 'high',
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                ],
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high',
                    ],
                ],
            ],
        ];

        $response = $this->httpClient->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $message,

            // ⚠️ مؤقتًا فقط لتعطيل تحقق SSL
            'verify' => false,
        ]);


        return json_decode($response->getBody(), true);
    }

    public function sendNotificationToAccount($accountId, $title, $body)
    {
        // جلب التوكن الخاص بالمستخدم
        $token = \App\Models\FcmToken::where('account_id', $accountId)->first();

        if (!$token) {
            Log::warning("FCM token not found for account_id: {$accountId}");
            return false;
        }

        Log::info("Sending notification to token: " . $token->token);
        return $this->sendNotificationToToken($token->token, $title, $body);
    }

    protected function getProjectId()
    {
    $config = json_decode(file_get_contents(app_path('firebase/acdivetsyria-18a18-c616f1fff90a.json')), true);
        return $config['project_id'];
    }
}
