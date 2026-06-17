<?php

namespace App\Application\Transaction\Transfer;

final readonly class TransferCommand
{
    public function __construct(
        public string $fromAccountId,
        public string $toAccountId,
        public int $amount,
        public string $currency,
        public string $apiClientId,
        public ?string $description = null,
        public ?string $idempotencyKey = null,
    ) {}
}
