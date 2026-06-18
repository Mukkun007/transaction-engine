<?php

namespace App\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateAccountInput
{
    #[Assert\NotBlank]
    public string $owner;

    #[Assert\NotBlank]
    #[Assert\Length(exactly: 3)]
    #[Assert\Currency]
    public string $currency;
}
