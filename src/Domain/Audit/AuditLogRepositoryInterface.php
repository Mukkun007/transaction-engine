<?php

namespace App\Domain\Audit;

interface AuditLogRepositoryInterface
{
    public function save(AuditLog $log): void;
}
