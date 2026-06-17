<?php

namespace App\Domain\Account;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'accounts')]
#[ORM\HasLifecycleCallbacks]
class Account
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $reference;

    #[ORM\Column(type: 'string')]
    private string $owner;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'string', enumType: AccountStatus::class)]
    private AccountStatus $status;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $reference, string $owner, string $currency)
    {
        $this->id = Uuid::v7();
        $this->reference = $reference;
        $this->owner = $owner;
        $this->currency = $currency;
        $this->status = AccountStatus::Active;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid { return $this->id; }
    public function getReference(): string { return $this->reference; }
    public function getOwner(): string { return $this->owner; }
    public function getCurrency(): string { return $this->currency; }
    public function getStatus(): AccountStatus { return $this->status; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function freeze(): void
    {
        $this->status = AccountStatus::Frozen;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function close(): void
    {
        $this->status = AccountStatus::Closed;
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
