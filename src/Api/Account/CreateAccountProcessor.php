<?php

namespace App\Api\Account;

use App\Api\Dto\CreateAccountInput;
use App\Application\Account\CreateAccount\CreateAccountCommand;
use App\Application\Account\CreateAccount\CreateAccountHandler;
use App\Infrastructure\Security\ApiClientUser;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;

/** @implements ProcessorInterface<CreateAccountInput, array<string, string>> */
final class CreateAccountProcessor implements ProcessorInterface
{
    public function __construct(
        private CreateAccountHandler $handler,
        private Security $security,
    ) {}

    /** @return array<string, string> */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var CreateAccountInput $data */
        /** @var ApiClientUser $user */
        $user = $this->security->getUser();

        $command = new CreateAccountCommand(
            owner: $data->owner,
            currency: $data->currency,
            apiClientId: $user->getUserIdentifier(),
        );

        $account = $this->handler->handle($command);

        return [
            'id' => (string) $account->getId(),
            'reference' => $account->getReference(),
            'owner' => $account->getOwner(),
            'currency' => $account->getCurrency(),
            'status' => $account->getStatus()->value,
            'createdAt' => $account->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
