<?php

namespace App\Domain\Auth;

enum ApiClientStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
}
