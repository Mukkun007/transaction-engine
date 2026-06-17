<?php

namespace App\Domain\Outbox;

interface OutboxRepositoryInterface
{
    public function save(OutboxMessage $message): void;
    /** @return OutboxMessage[] */
    public function findPending(): array;
}
