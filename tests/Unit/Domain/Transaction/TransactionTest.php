<?php

namespace App\Tests\Unit\Domain\Transaction;

use App\Domain\Auth\ApiClient;
use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\TransactionStatus;
use App\Domain\Transaction\TransactionType;
use PHPUnit\Framework\TestCase;

final class TransactionTest extends TestCase
{
    private function makeApiClient(): ApiClient
    {
        return new ApiClient('Test Client', 'test-key');
    }

    public function testTransactionIsCreatedWithPendingStatus(): void
    {
        $transaction = new Transaction(
            reference: 'TXN-001',
            type: TransactionType::Deposit,
            amount: 10000,
            currency: 'EUR',
            apiClient: $this->makeApiClient(),
        );

        $this->assertSame(TransactionStatus::Pending, $transaction->getStatus());
        $this->assertSame(10000, $transaction->getAmount());
        $this->assertSame('EUR', $transaction->getCurrency());
    }

    public function testTransactionCanBeCompleted(): void
    {
        $transaction = new Transaction(
            reference: 'TXN-001',
            type: TransactionType::Deposit,
            amount: 10000,
            currency: 'EUR',
            apiClient: $this->makeApiClient(),
        );

        $transaction->complete();

        $this->assertSame(TransactionStatus::Completed, $transaction->getStatus());
    }

    public function testTransactionCanBeFailed(): void
    {
        $transaction = new Transaction(
            reference: 'TXN-001',
            type: TransactionType::Deposit,
            amount: 10000,
            currency: 'EUR',
            apiClient: $this->makeApiClient(),
        );

        $transaction->fail();

        $this->assertSame(TransactionStatus::Failed, $transaction->getStatus());
    }

    public function testTransactionCanBeReversed(): void
    {
        $original = new Transaction(
            reference: 'TXN-001',
            type: TransactionType::Deposit,
            amount: 10000,
            currency: 'EUR',
            apiClient: $this->makeApiClient(),
        );

        $original->complete();

        $reversal = new Transaction(
            reference: 'TXN-REV-001',
            type: TransactionType::Deposit,
            amount: 10000,
            currency: 'EUR',
            apiClient: $this->makeApiClient(),
        );

        $original->reverse($reversal);

        $this->assertSame(TransactionStatus::Reversed, $original->getStatus());
        $this->assertSame($reversal, $original->getRelatedTransaction());
    }
}
