<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private string $fcmUrl = 'https://fcm.googleapis.com/v1/projects/
mealtrack-9a08b/messages:send';

    /**
     * Send push notification to a single user
     */
    public function sendToUser(User $user, string $title, string $body): void
    {
        if (!$user->fcm_token) return;

        $this->send($user->fcm_token, $title, $body);
    }

    /**
     * Send cutoff reminder to all residents with pending bookings
     */
    public function sendCutoffReminders(): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, User> $users */
        $users = User::whereHas('pendingBookings')
                     ->whereNotNull('fcm_token')
                     ->get();

        $users->each(function (User $user): void {
            $this->sendToUser(
                $user,
                'Booking cutoff approaching!',
                'You have pending meal bookings. Check in before the cutoff.'
            );
        });
    }

    /**
     * Send low credit warning
     */
    public function sendLowCreditWarning(User $user): void
    {
        if ($user->wallet->balance <= 2) {
            $this->sendToUser(
                $user,
                'Low meal credits',
                "You only have {$user->wallet->balance} credits remaining. Contact admin to top up."
            );
        }
    }

    private function send(string $fcmToken, string $title, string $body): void
    {
        try {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type'  => 'application/json',
            ])->post($this->fcmUrl, [
                'message' => [
                    'token'        => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('FCM send failed: ' . $e->getMessage());
        }
    }

    private function getAccessToken(): string
    {
        // Use Firebase Admin SDK service account
        // Store your service account JSON in storage/app/firebase-credentials.json
        $credentials = json_decode(
            file_get_contents(storage_path('app/firebase-credentials.json')),
            true
        );

        // Generate OAuth2 token from service account
        $client = new \Google\Auth\Credentials\ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $credentials
        );

        return $client->fetchAuthToken()['access_token'];
    }
}