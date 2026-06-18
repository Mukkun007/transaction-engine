<?php

namespace App\Domain\Audit;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'audit_logs')]
class AuditLog
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string')]
    private string $entityType;

    #[ORM\Column(type: 'string')]
    private string $entityId;

    #[ORM\Column(type: 'string', enumType: AuditAction::class)]
    private AuditAction $action;

    #[ORM\Column(name: 'before_data', type: 'json', nullable: true)]
    private ?array $before;

    #[ORM\Column(name: 'after_data', type: 'json')]
    private array $after;

    #[ORM\Column(type: 'string')]
    private string $performedBy;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $entityType,
        string $entityId,
        AuditAction $action,
        array $after,
        string $performedBy,
        ?array $before = null,
    ) {
        $this->id = Uuid::v7();
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->action = $action;
        $this->before = $before;
        $this->after = $after;
        $this->performedBy = $performedBy;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid { return $this->id; }
    public function getEntityType(): string { return $this->entityType; }
    public function getEntityId(): string { return $this->entityId; }
    public function getAction(): AuditAction { return $this->action; }
    public function getBefore(): ?array { return $this->before; }
    public function getAfter(): array { return $this->after; }
    public function getPerformedBy(): string { return $this->performedBy; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
