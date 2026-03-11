<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrevoService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $brevoApiKey,
    ) {}

    public function syncContact(string $email): void
    {
        try {
            $this->httpClient->request('POST', 'https://api.brevo.com/v3/contacts', [
                'headers' => [
                    'api-key'      => $this->brevoApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email'         => $email,
                    'updateEnabled' => true,
                    'listIds'       => [2], // ID de ta liste newsletter dans Brevo
                ],
            ]);
        } catch (\Throwable) {
            // On ne bloque pas l'inscription si Brevo est indisponible
        }
    }

    public function unsubscribeContact(string $email): void
    {
        try {
            $this->httpClient->request('PUT', 'https://api.brevo.com/v3/contacts/' . urlencode($email), [
                'headers' => [
                    'api-key'      => $this->brevoApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'emailBlacklisted' => true,
                ],
            ]);
        } catch (\Throwable) {
            // On ne bloque pas la désinscription si Brevo est indisponible
        }
    }
}