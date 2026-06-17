<?php

namespace App\Domain\Transaction;

use App\Domain\Account\Account;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'entries')]
class Entry
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Account $account;

    #[ORM\ManyToOne(targetEntity: Transaction::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Transaction $transaction;

    #[ORM\Column(type: 'string', enumType: EntryType::class)]
    private EntryType $type;

    #[ORM\Column(type: 'integer')]
    private int $amount;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Account $account,
        Transaction $transaction,
        EntryType $type,
        int $amount,
        string $currency,
    ) {
        $this->id = Uuid::v7();
        $this->account = $account;
        $this->transaction = $transaction;
        $this->type = $type;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid { return $this->id; }
    public function getAccount(): Account { return $this->account; }
    public function getTransaction(): Transaction { return $this->transaction; }
    public function getType(): EntryType { return $this->type; }
    public function getAmount(): int { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
