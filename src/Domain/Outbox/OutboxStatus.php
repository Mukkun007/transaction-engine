<?php

namespace App\Domain\Outbox;

enum OutboxStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}
