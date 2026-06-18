<?php

namespace App\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class TransferInput
{
    #[Assert\NotBlank]
    public string $fromAccountId;

    #[Assert\NotBlank]
    public string $toAccountId;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $amount;

    #[Assert\NotBlank]
    #[Assert\Length(exactly: 3)]
    public string $currency;

    public ?string $description = null;
    public ?string $idempotencyKey = null;
}
