<?php

namespace App\Tests\Integration\Application\Transaction;

use App\Application\Account\CreateAccount\CreateAccountCommand;
use App\Application\Account\CreateAccount\CreateAccountHandler;
use App\Application\Transaction\Deposit\DepositCommand;
use App\Application\Transaction\Deposit\DepositHandler;
use App\Application\Transaction\Transfer\TransferCommand;
use App\Application\Transaction\Transfer\TransferHandler;
use App\Domain\Auth\ApiClient;
use App\Domain\Auth\ApiClientRepositoryInterface;
use App\Domain\Transaction\TransactionStatus;
use App\Domain\Transaction\TransactionType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TransferHandlerTest extends KernelTestCase
{
    private TransferHandler $handler;
    private DepositHandler $depositHandler;
    private CreateAccountHandler $createAccountHandler;
    private ApiClientRepositoryInterface $apiClientRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(TransferHandler::class);
        $this->depositHandler = $container->get(DepositHandler::class);
        $this->createAccountHandler = $container->get(CreateAccountHandler::class);
        $this->apiClientRepository = $container->get(ApiClientRepositoryInterface::class);
    }

    public function testTransferMovesAmountBetweenAccounts(): void
    {
        $apiClient = new ApiClient('Test Client', 'test-key-transfer');
        $this->apiClientRepository->save($apiClient);

        $fromAccount = $this->createAccountHandler->handle(new CreateAccountCommand(
            owner: 'Sender',
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $toAccount = $this->createAccountHandler->handle(new CreateAccountCommand(
            owner: 'Receiver',
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $this->depositHandler->handle(new DepositCommand(
            accountId: (string) $fromAccount->getId(),
            amount: 10000,
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $transaction = $this->handler->handle(new TransferCommand(
            fromAccountId: (string) $fromAccount->getId(),
            toAccountId: (string) $toAccount->getId(),
            amount: 5000,
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $this->assertSame(TransactionStatus::Completed, $transaction->getStatus());
        $this->assertSame(TransactionType::Transfer, $transaction->getType());
        $this->assertSame(5000, $transaction->getAmount());
    }

    public function testTransferFailsWhenInsufficientFunds(): void
    {
        $apiClient = new ApiClient('Test Client', 'test-key-transfer-fail');
        $this->apiClientRepository->save($apiClient);

        $fromAccount = $this->createAccountHandler->handle(new CreateAccountCommand(
            owner: 'Sender',
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $toAccount = $this->createAccountHandler->handle(new CreateAccountCommand(
            owner: 'Receiver',
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Insufficient funds.');

        $this->handler->handle(new TransferCommand(
            fromAccountId: (string) $fromAccount->getId(),
            toAccountId: (string) $toAccount->getId(),
            amount: 5000,
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));
    }
}
