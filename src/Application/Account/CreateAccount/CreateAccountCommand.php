<?php

namespace App\Application\Account\CreateAccount;

final readonly class CreateAccountCommand
{
    public function __construct(
        public string $owner,
        public string $currency,
        public string $apiClientId,
    ) {}
}
