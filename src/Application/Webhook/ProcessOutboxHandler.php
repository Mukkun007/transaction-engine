<?php

namespace App\Application\Webhook;

use App\Domain\Outbox\OutboxRepositoryInterface;
use App\Infrastructure\Http\WebhookSender;

final class ProcessOutboxHandler
{
    public function __construct(
        private OutboxRepositoryInterface $outboxRepository,
        private WebhookSender $webhookSender,
    ) {}

    public function process(): void
    {
        $messages = $this->outboxRepository->findPending();

        foreach ($messages as $message) {
            try {
                $this->webhookSender->send(
                    $message->getEventType(),
                    $message->getPayload()
                );
                $message->markAsSent();
            } catch (\Throwable) {
                $message->markAsFailed();
            }

            $this->outboxRepository->save($message);
        }
    }
}
