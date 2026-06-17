<?php

namespace App\Shared;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
