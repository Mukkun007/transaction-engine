<?php

namespace App\Domain\Transaction;

use Symfony\Component\Uid\Uuid;

interface TransactionRepositoryInterface
{
    public function findById(Uuid $id): ?Transaction;
    public function findByReference(string $reference): ?Transaction;
    public function save(Transaction $transaction): void;
}
