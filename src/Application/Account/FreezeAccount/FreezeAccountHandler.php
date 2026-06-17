<?php

namespace App\Application\Account\FreezeAccount;

use App\Domain\Account\AccountRepositoryInterface;
use App\Domain\Audit\AuditAction;
use App\Domain\Audit\AuditLog;
use App\Domain\Audit\AuditLogRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final class FreezeAccountHandler
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function handle(FreezeAccountCommand $command): void
    {
        $account = $this->accountRepository->findById(
            Uuid::fromString($command->accountId)
        );

        if ($account === null) {
            throw new \InvalidArgumentException('Account not found.');
        }

        $before = ['status' => $account->getStatus()->value];

        $account->freeze();
        $this->accountRepository->save($account);

        $this->auditLogRepository->save(new AuditLog(
            entityType: 'Account',
            entityId: $command->accountId,
            action: AuditAction::StatusChanged,
            after: ['status' => $account->getStatus()->value],
            performedBy: $command->apiClientId,
            before: $before,
        ));
    }
}
