<?php

namespace App\Api\Dto;

final class TransactionOutput
{
    public function __construct(
        public string $id,
        public string $reference,
        public string $type,
        public int $amount,
        public string $currency,
        public string $status,
        public ?string $description,
        public string $createdAt,
    ) {}
}
