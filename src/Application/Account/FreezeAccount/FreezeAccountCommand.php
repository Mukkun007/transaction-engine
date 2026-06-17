<?php

namespace App\Application\Account\FreezeAccount;

final readonly class FreezeAccountCommand
{
    public function __construct(
        public string $accountId,
        public string $apiClientId,
    ) {}
}
