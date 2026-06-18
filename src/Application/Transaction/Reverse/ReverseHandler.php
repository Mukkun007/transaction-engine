<?php

namespace App\Application\Transaction\Reverse;

use App\Domain\Account\AccountRepositoryInterface;
use App\Domain\Audit\AuditAction;
use App\Domain\Audit\AuditLog;
use App\Domain\Audit\AuditLogRepositoryInterface;
use App\Domain\Auth\ApiClientRepositoryInterface;
use App\Domain\Transaction\Entry;
use App\Domain\Transaction\EntryType;
use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\TransactionRepositoryInterface;
use App\Domain\Transaction\TransactionStatus;
use App\Domain\Transaction\TransactionType;
use Symfony\Component\Uid\Uuid;

final class ReverseHandler
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private AccountRepositoryInterface $accountRepository,
        private AuditLogRepositoryInterface $auditLogRepository,
        private ApiClientRepositoryInterface $apiClientRepository,
    ) {}

    public function handle(ReverseCommand $command): Transaction
    {
        $original = $this->transactionRepository->findById(
            Uuid::fromString($command->transactionId)
        );

        if ($original === null) {
            throw new \InvalidArgumentException('Transaction not found.');
        }

        if ($original->getStatus() !== TransactionStatus::Completed) {
            throw new \DomainException('Only completed transactions can be reversed.');
        }

        $apiClient = $this->apiClientRepository->findById(
            Uuid::fromString($command->apiClientId)
        );

        if ($apiClient === null) {
            throw new \InvalidArgumentException('ApiClient not found.');
        }

        $reference = 'TXN-REV-' . strtoupper(substr(uniqid(), -8));

        // Crée une transaction inverse
        $reversal = new Transaction(
            reference: $reference,
            type: $original->getType(),
            amount: $original->getAmount(),
            currency: $original->getCurrency(),
            apiClient: $apiClient,
            description: 'Reversal of ' . $original->getReference(),
        );

        // Inverse les écritures comptables
        foreach ($this->getEntriesForTransaction($original) as $originalEntry) {
            $reversedType = $originalEntry->getType() === EntryType::Credit
                ? EntryType::Debit
                : EntryType::Credit;

            new Entry(
                account: $originalEntry->getAccount(),
                transaction: $reversal,
                type: $reversedType,
                amount: $originalEntry->getAmount(),
                currency: $originalEntry->getCurrency(),
            );
        }

        $reversal->complete();
        $original->reverse($reversal);

        $this->transactionRepository->save($reversal);
        $this->transactionRepository->save($original);

        $this->auditLogRepository->save(new AuditLog(
            entityType: 'Transaction',
            entityId: (string) $original->getId(),
            action: AuditAction::Reversed,
            after: ['status' => $original->getStatus()->value, 'reversalId' => (string) $reversal->getId()],
            performedBy: $command->apiClientId,
            before: ['status' => TransactionStatus::Completed->value],
        ));

        return $reversal;
    }

    private function getEntriesForTransaction(Transaction $transaction): array
    {
        return $transaction->getEntries()->toArray();
    }
}
