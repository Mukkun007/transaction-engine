<?php

namespace App\Domain\Account;

use Symfony\Component\Uid\Uuid;

interface AccountRepositoryInterface
{
    public function findById(Uuid $id): ?Account;
    public function findByReference(string $reference): ?Account;
    public function save(Account $account): void;
}
