<?php

namespace App\Application\Transaction\Withdraw;

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

final class WithdrawHandler
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private TransactionRepositoryInterface $transactionRepository,
        private AuditLogRepositoryInterface $auditLogRepository,
        private ApiClientRepositoryInterface $apiClientRepository,
        private EntityManagerInterface $em,
    ) {}

    public function handle(WithdrawCommand $command): Transaction
    {
        // Verrouillage pessimiste pour éviter les race conditions
        $account = $this->em->getRepository(Account::class)
            ->find(Uuid::fromString($command->accountId), \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);

        if ($account === null) {
            throw new \InvalidArgumentException('Account not found.');
        }

        $apiClient = $this->apiClientRepository->findById(
            Uuid::fromString($command->apiClientId)
        );

        if ($apiClient === null) {
            throw new \InvalidArgumentException('ApiClient not found.');
        }

        // Calcul du solde depuis les écritures
        $balance = $this->calculateBalance($account);

        if ($balance < $command->amount) {
            throw new \DomainException('Insufficient funds.');
        }

        $reference = 'TXN-' . strtoupper(substr(uniqid(), -8));

        $transaction = new Transaction(
            reference: $reference,
            type: TransactionType::Withdrawal,
            amount: $command->amount,
            currency: $command->currency,
            apiClient: $apiClient,
            description: $command->description,
        );

        // Double-entry : un débit sur le compte
        $entry = new Entry(
            account: $account,
            transaction: $transaction,
            type: EntryType::Debit,
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
            ],
            performedBy: $command->apiClientId,
        ));

        return $transaction;
    }

    private function calculateBalance(Account $account): int
    {
        $result = $this->em->createQueryBuilder()
            ->select('SUM(CASE WHEN e.type = :credit THEN e.amount ELSE -e.amount END) as balance')
            ->from(Entry::class, 'e')
            ->where('e.account = :account')
            ->setParameter('account', $account)
            ->setParameter('credit', EntryType::Credit->value)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }
}
