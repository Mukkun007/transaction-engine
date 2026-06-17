<?php

namespace App\Domain\Auth;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'api_clients')]
class ApiClient
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string', unique: true)]
    private string $apiKey;

    #[ORM\Column(type: 'string', enumType: ApiClientStatus::class)]
    private ApiClientStatus $status;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $name, string $apiKey)
    {
        $this->id = Uuid::v7();
        $this->name = $name;
        $this->apiKey = $apiKey;
        $this->status = ApiClientStatus::Active;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getApiKey(): string { return $this->apiKey; }
    public function getStatus(): ApiClientStatus { return $this->status; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function revoke(): void
    {
        $this->status = ApiClientStatus::Revoked;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
