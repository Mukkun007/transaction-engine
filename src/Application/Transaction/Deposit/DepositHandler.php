<?php

namespace App\Application\Transaction\Deposit;

use App\Domain\Account\AccountRepositoryInterface;
use App\Domain\Audit\AuditAction;
use App\Domain\Audit\AuditLog;
use App\Domain\Audit\AuditLogRepositoryInterface;
use App\Domain\Auth\ApiClientRepositoryInterface;
use App\Domain\Transaction\Entry;
use App\Domain\Transaction\EntryType;
use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\TransactionRepositoryInterface;
use App\Domain\Transaction\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class DepositHandler
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private TransactionRepositoryInterface $transactionRepository,
        private AuditLogRepositoryInterface $auditLogRepository,
        private ApiClientRepositoryInterface $apiClientRepository,
        private EntityManagerInterface $em,
    ) {}

    public function handle(DepositCommand $command): Transaction
    {
        $account = $this->accountRepository->findById(
            Uuid::fromString($command->accountId)
        );

        if ($account === null) {
            throw new \InvalidArgumentException('Account not found.');
        }

        $apiClient = $this->apiClientRepository->findById(
            Uuid::fromString($command->apiClientId)
        );

        if ($apiClient === null) {
            throw new \InvalidArgumentException('ApiClient not found.');
        }

        $reference = 'TXN-' . strtoupper(substr(uniqid(), -8));

        $transaction = new Transaction(
            reference: $reference,
            type: TransactionType::Deposit,
            amount: $command->amount,
            currency: $command->currency,
            apiClient: $apiClient,
            description: $command->description,
        );

        $entry = new Entry(
            account: $account,
            transaction: $transaction,
            type: EntryType::Credit,
            amount: $command->amount,
            currency: $command->currency,
        );

        $transaction->complete();
        $this->em->persist($entry);
        $this->transactionRepository->save($transaction);

        $this->auditLogRepository->save(new AuditLog(
            entityType: 'Transaction',
            entityId: (string) $transaction->getId(),
            action: AuditAction::Created,
            after: [
                'reference' => $transaction->getReference(),
                'type' => $transaction->getType()->value,
                'amount' => $transaction->getAmount(),
                'currency' => $transaction->getCurrency(),
                'status' => $transaction->getStatus()->value,
            ],
            performedBy: $command->apiClientId,
        ));

        return $transaction;
    }
}
