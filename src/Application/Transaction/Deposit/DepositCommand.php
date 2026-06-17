<?php

namespace App\Application\Transaction\Deposit;

final readonly class DepositCommand
{
    public function __construct(
        public string $accountId,
        public int $amount,
        public string $currency,
        public string $apiClientId,
        public ?string $description = null,
        public ?string $idempotencyKey = null,
    ) {}
}
