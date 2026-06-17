<?php

namespace App\Domain\Transaction;

enum EntryType: string
{
    case Debit = 'debit';
    case Credit = 'credit';
}
