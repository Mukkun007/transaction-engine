<?php

namespace App\Api\Transaction;

use App\Api\Dto\DepositInput;
use App\Api\Dto\TransactionOutput;
use App\Application\Transaction\Withdraw\WithdrawCommand;
use App\Application\Transaction\Withdraw\WithdrawHandler;
use App\Infrastructure\Security\ApiClientUser;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;

/** @implements ProcessorInterface<DepositInput, TransactionOutput> */
final class WithdrawProcessor implements ProcessorInterface
{
    public function __construct(
        private WithdrawHandler $handler,
        private Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TransactionOutput
    {   
        /** @var DepositInput $data */
        /** @var ApiClientUser $user */
        $user = $this->security->getUser();

        $transaction = $this->handler->handle(new WithdrawCommand(
            accountId: $data->accountId,
            amount: $data->amount,
            currency: $data->currency,
            apiClientId: $user->getUserIdentifier(),
            description: $data->description,
            idempotencyKey: $data->idempotencyKey,
        ));

        return new TransactionOutput(
            id: (string) $transaction->getId(),
            reference: $transaction->getReference(),
            type: $transaction->getType()->value,
            amount: $transaction->getAmount(),
            currency: $transaction->getCurrency(),
            status: $transaction->getStatus()->value,
            description: $transaction->getDescription(),
            createdAt: $transaction->getCreatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
