<?php

namespace App\Domain\Audit;

enum AuditAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case StatusChanged = 'status_changed';
    case Reversed = 'reversed';
}
