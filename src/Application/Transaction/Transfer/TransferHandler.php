<?php

namespace App\Application\Transaction\Transfer;

use App\Domain\Account\Account;
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

final class TransferHandler
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private TransactionRepositoryInterface $transactionRepository,
        private AuditLogRepositoryInterface $auditLogRepository,
        private ApiClientRepositoryInterface $apiClientRepository,
        private EntityManagerInterface $em,
    ) {}

    public function handle(TransferCommand $command): Transaction
    {
        return $this->em->wrapInTransaction(function() use ($command) {
            $fromAccount = $this->em->getRepository(Account::class)
                ->find(Uuid::fromString($command->fromAccountId), \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);

            $toAccount = $this->em->getRepository(Account::class)
                ->find(Uuid::fromString($command->toAccountId), \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);

            if ($fromAccount === null || $toAccount === null) {
                throw new \InvalidArgumentException('One or both accounts not found.');
            }

            $apiClient = $this->apiClientRepository->findById(
                Uuid::fromString($command->apiClientId)
            );

            if ($apiClient === null) {
                throw new \InvalidArgumentException('ApiClient not found.');
            }

            $balance = $this->calculateBalance($fromAccount);

            if ($balance < $command->amount) {
                throw new \DomainException('Insufficient funds.');
            }

            $reference = 'TXN-' . strtoupper(substr(uniqid(), -8));

            $transaction = new Transaction(
                reference: $reference,
                type: TransactionType::Transfer,
                amount: $command->amount,
                currency: $command->currency,
                apiClient: $apiClient,
                description: $command->description,
            );

            new Entry(
                account: $fromAccount,
                transaction: $transaction,
                type: EntryType::Debit,
                amount: $command->amount,
                currency: $command->currency,
            );

            new Entry(
                account: $toAccount,
                transaction: $transaction,
                type: EntryType::Credit,
                amount: $command->amount,
                currency: $command->currency,
            );

            $transaction->complete();
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
                    'from' => $command->fromAccountId,
                    'to' => $command->toAccountId,
                ],
                performedBy: $command->apiClientId,
            ));

            return $transaction;
        });
    }

    private function calculateBalance(Account $account): int
    {
        $result = $this->em->createQueryBuilder()
            ->select('SUM(CASE WHEN e.type = :credit THEN e.amount ELSE -e.amount END) as balance')
            ->from(Entry::class, 'e')
            ->where('IDENTITY(e.account) = :accountId')
            ->setParameter('accountId', $account->getId(), 'uuid')
            ->setParameter('credit', EntryType::Credit->value)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }
}
