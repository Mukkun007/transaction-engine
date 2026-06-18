<?php

namespace App\Application\Transaction\Reverse;

final readonly class ReverseCommand
{
    public function __construct(
        public string $transactionId,
        public string $apiClientId,
    ) {}
}
