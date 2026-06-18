<?php

namespace App\Api\Transaction;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Api\Dto\DepositInput;
use App\Api\Dto\TransferInput;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/transactions/deposit',
            input: DepositInput::class,
            processor: DepositProcessor::class,
        ),
        new Post(
            uriTemplate: '/transactions/withdraw',
            input: DepositInput::class,
            processor: WithdrawProcessor::class,
        ),
        new Post(
            uriTemplate: '/transactions/transfer',
            input: TransferInput::class,
            processor: TransferProcessor::class,
        ),
        new Post(
            uriTemplate: '/transactions/{id}/reverse',
            input: false,
            processor: ReverseProcessor::class,
        ),
    ]
)]
final class TransactionResource
{
}
