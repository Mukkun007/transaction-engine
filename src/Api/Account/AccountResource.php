<?php

namespace App\Api\Account;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Api\Dto\CreateAccountInput;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/accounts',
            input: CreateAccountInput::class,
            processor: CreateAccountProcessor::class,
        ),
    ]
)]
final class AccountResource
{
}
