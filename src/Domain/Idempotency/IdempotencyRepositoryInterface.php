<?php

namespace App\Domain\Idempotency;

interface IdempotencyRepositoryInterface
{
    public function findByKeyAndClient(string $keyValue, string $apiClientId): ?IdempotencyKey;
    public function save(IdempotencyKey $key): void;
}
