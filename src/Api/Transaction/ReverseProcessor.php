<?php

namespace App\Api\Transaction;

use App\Api\Dto\TransactionOutput;
use App\Application\Transaction\Reverse\ReverseCommand;
use App\Application\Transaction\Reverse\ReverseHandler;
use App\Infrastructure\Security\ApiClientUser;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class ReverseProcessor implements ProcessorInterface
{
    public function __construct(
        private ReverseHandler $handler,
        private Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TransactionOutput
    {
        /** @var ApiClientUser $user */
        $user = $this->security->getUser();

        $transaction = $this->handler->handle(new ReverseCommand(
            transactionId: $uriVariables['id'],
            apiClientId: $user->getUserIdentifier(),
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
