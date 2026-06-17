<?php

namespace App\Application\Account\CreateAccount;

use App\Domain\Account\Account;
use App\Domain\Account\AccountRepositoryInterface;
use App\Domain\Audit\AuditAction;
use App\Domain\Audit\AuditLog;
use App\Domain\Audit\AuditLogRepositoryInterface;
use App\Domain\Auth\ApiClientRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final class CreateAccountHandler
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private AuditLogRepositoryInterface $auditLogRepository,
        private ApiClientRepositoryInterface $apiClientRepository,
    ) {}

    public function handle(CreateAccountCommand $command): Account
    {
        $apiClient = $this->apiClientRepository->findById(
            Uuid::fromString($command->apiClientId)
        );

        if ($apiClient === null) {
            throw new \InvalidArgumentException('ApiClient not found.');
        }

        $reference = 'ACC-' . strtoupper(substr(uniqid(), -6));

        $account = new Account(
            reference: $reference,
            owner: $command->owner,
            currency: $command->currency,
        );

        $this->accountRepository->save($account);

        $this->auditLogRepository->save(new AuditLog(
            entityType: 'Account',
            entityId: (string) $account->getId(),
            action: AuditAction::Created,
            after: [
                'reference' => $account->getReference(),
                'owner' => $account->getOwner(),
                'currency' => $account->getCurrency(),
                'status' => $account->getStatus()->value,
            ],
            performedBy: $command->apiClientId,
        ));

        return $account;
    }
}
