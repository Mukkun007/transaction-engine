<?php

namespace App\Domain\Account;

enum AccountStatus: string
{
    case Active = 'active';
    case Frozen = 'frozen';
    case Closed = 'closed';
}
