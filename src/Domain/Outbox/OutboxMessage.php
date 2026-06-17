<?php

namespace App\Domain\Outbox;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use App\Domain\Outbox\OutboxStatus;

#[ORM\Entity]
#[ORM\Table(name: 'outbox_messages')]
class OutboxMessage
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string')]
    private string $eventType;

    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(type: 'string', enumType: OutboxStatus::class)]
    private OutboxStatus $status;

    #[ORM\Column(type: 'integer')]
    private int $attempts;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastAttemptAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $eventType, array $payload)
    {
        $this->id = Uuid::v7();
        $this->eventType = $eventType;
        $this->payload = $payload;
        $this->status = OutboxStatus::Pending;
        $this->attempts = 0;
        $this->lastAttemptAt = null;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid { return $this->id; }
    public function getEventType(): string { return $this->eventType; }
    public function getPayload(): array { return $this->payload; }
    public function getStatus(): OutboxStatus { return $this->status; }
    public function getAttempts(): int { return $this->attempts; }
    public function getLastAttemptAt(): ?\DateTimeImmutable { return $this->lastAttemptAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function markAsSent(): void
    {
        $this->status = OutboxStatus::Sent;
        $this->attempts++;
        $this->lastAttemptAt = new \DateTimeImmutable();
    }

    public function markAsFailed(): void
    {
        $this->status = OutboxStatus::Failed;
        $this->attempts++;
        $this->lastAttemptAt = new \DateTimeImmutable();
    }
}
