<?php

namespace App\Shared;

use Symfony\Component\Uid\Uuid;

class IdGenerator
{
    public static function generate(): Uuid
    {
        return Uuid::v7();
    }
}
