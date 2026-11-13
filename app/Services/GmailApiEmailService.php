<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GmailApiEmailService
{
    public function isConfigured(): bool
    {
        return filled(config('services.gmail_api.client_id'))
            && filled(config('services.gmail_api.client_secret'))
            && filled(config('services.gmail_api.refresh_token'))
            && filled(config('services.gmail_api.sender_address'));
    }

    public function sendPasswordRecoveryCode(User $user, string $code): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Gmail API OAuth credentials are not configured.');
        }

        $sender = (string) config('services.gmail_api.sender_address');
        if (preg_match('/[\r\n]/', $sender)) {
            throw new RuntimeException('Gmail API sender address is invalid.');
        }

        $tokenResponse = Http::asForm()
            ->connectTimeout(8)
            ->timeout(15)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.gmail_api.client_id'),
                'client_secret' => config('services.gmail_api.client_secret'),
                'refresh_token' => config('services.gmail_api.refresh_token'),
                'grant_type' => 'refresh_token',
            ]);

        if (! $tokenResponse->successful() || ! is_string($tokenResponse->json('access_token'))) {
            throw new RuntimeException('Gmail API authorization failed with HTTP '.$tokenResponse->status().'.');
        }

        $fromName = (string) config('mail.from.name', 'GPMO Law Offices');
        $subject = 'Your password recovery code';
        $body = "Your GPMO password recovery code is: {$code}\n\n"
            . "This code expires in 15 minutes and can be used once.\n"
            . "If you did not request a password reset, you can ignore this message.";
        $mimeMessage = implode("\r\n", [
            'From: =?UTF-8?B?'.base64_encode($fromName)."?= <{$sender}>",
            'To: '.$user->email,
            'Subject: '.$subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            chunk_split(base64_encode($body), 76, "\r\n"),
        ]);
        $rawMessage = rtrim(strtr(base64_encode($mimeMessage), '+/', '-_'), '=');

        $sendResponse = Http::withToken($tokenResponse->json('access_token'))
            ->acceptJson()
            ->connectTimeout(8)
            ->timeout(15)
            ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                'raw' => $rawMessage,
            ]);

        if (! $sendResponse->successful()) {
            throw new RuntimeException('Gmail API rejected the email with HTTP '.$sendResponse->status().'.');
        }
    }
}
