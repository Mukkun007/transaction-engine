<?php

namespace App\Domain\Idempotency;

use App\Domain\Auth\ApiClient;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'idempotency_keys')]
class IdempotencyKey
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $keyValue;

    #[ORM\ManyToOne(targetEntity: ApiClient::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ApiClient $apiClient;

    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(type: 'integer')]
    private int $responseCode;

    #[ORM\Column(type: 'json')]
    private array $responseBody;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $keyValue,
        ApiClient $apiClient,
        array $payload,
        int $responseCode,
        array $responseBody,
        \DateTimeImmutable $expiresAt,
    ) {
        $this->id = Uuid::v7();
        $this->keyValue = $keyValue;
        $this->apiClient = $apiClient;
        $this->payload = $payload;
        $this->responseCode = $responseCode;
        $this->responseBody = $responseBody;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid { return $this->id; }
    public function getKeyValue(): string { return $this->keyValue; }
    public function getApiClient(): ApiClient { return $this->apiClient; }
    public function getPayload(): array { return $this->payload; }
    public function getResponseCode(): int { return $this->responseCode; }
    public function getResponseBody(): array { return $this->responseBody; }
    public function getExpiresAt(): \DateTimeImmutable { return $this->expiresAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
