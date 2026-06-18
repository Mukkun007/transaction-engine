<?php

namespace App\Infrastructure\Http;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WebhookSender
{
    private ?string $webhookUrl;

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
        $this->webhookUrl = $_ENV['WEBHOOK_URL'] ?? null;
    }

    public function send(string $eventType, array $payload): void
    {
        if ($this->webhookUrl === null) {
            return;
        }

        $this->httpClient->request('POST', $this->webhookUrl, [
            'json' => [
                'event' => $eventType,
                'payload' => $payload,
                'sentAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ],
        ]);
    }
}
