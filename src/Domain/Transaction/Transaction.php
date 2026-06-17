<?php

namespace App\Domain\Transaction;

use App\Domain\Auth\ApiClient;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'transactions')]
class Transaction
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $reference;

    #[ORM\Column(type: 'string', enumType: TransactionType::class)]
    private TransactionType $type;

    #[ORM\Column(type: 'integer')]
    private int $amount;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'string', enumType: TransactionStatus::class)]
    private TransactionStatus $status;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description;

    #[ORM\ManyToOne(targetEntity: Transaction::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Transaction $relatedTransaction;

    #[ORM\ManyToOne(targetEntity: ApiClient::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ApiClient $apiClient;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $reference,
        TransactionType $type,
        int $amount,
        string $currency,
        ApiClient $apiClient,
        ?string $description = null,
    ) {
        $this->id = Uuid::v7();
        $this->reference = $reference;
        $this->type = $type;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->status = TransactionStatus::Pending;
        $this->apiClient = $apiClient;
        $this->description = $description;
        $this->relatedTransaction = null;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid { return $this->id; }
    public function getReference(): string { return $this->reference; }
    public function getType(): TransactionType { return $this->type; }
    public function getAmount(): int { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getStatus(): TransactionStatus { return $this->status; }
    public function getDescription(): ?string { return $this->description; }
    public function getRelatedTransaction(): ?Transaction { return $this->relatedTransaction; }
    public function getApiClient(): ApiClient { return $this->apiClient; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function complete(): void
    {
        $this->status = TransactionStatus::Completed;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function fail(): void
    {
        $this->status = TransactionStatus::Failed;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function reverse(Transaction $reversal): void
    {
        $this->status = TransactionStatus::Reversed;
        $this->relatedTransaction = $reversal;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
