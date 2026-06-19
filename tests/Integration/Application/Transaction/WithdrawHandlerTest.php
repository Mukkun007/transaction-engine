<?php

namespace App\Tests\Integration\Application\Transaction;

use App\Application\Account\CreateAccount\CreateAccountCommand;
use App\Application\Account\CreateAccount\CreateAccountHandler;
use App\Application\Transaction\Deposit\DepositCommand;
use App\Application\Transaction\Deposit\DepositHandler;
use App\Application\Transaction\Withdraw\WithdrawCommand;
use App\Application\Transaction\Withdraw\WithdrawHandler;
use App\Domain\Auth\ApiClient;
use App\Domain\Auth\ApiClientRepositoryInterface;
use App\Domain\Transaction\TransactionStatus;
use App\Domain\Transaction\TransactionType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class WithdrawHandlerTest extends KernelTestCase
{
    private WithdrawHandler $handler;
    private DepositHandler $depositHandler;
    private CreateAccountHandler $createAccountHandler;
    private ApiClientRepositoryInterface $apiClientRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(WithdrawHandler::class);
        $this->depositHandler = $container->get(DepositHandler::class);
        $this->createAccountHandler = $container->get(CreateAccountHandler::class);
        $this->apiClientRepository = $container->get(ApiClientRepositoryInterface::class);
    }

    public function testWithdrawCreatesCompletedTransaction(): void
    {
        $apiClient = new ApiClient('Test Client', 'test-key-withdraw');
        $this->apiClientRepository->save($apiClient);

        $account = $this->createAccountHandler->handle(new CreateAccountCommand(
            owner: 'John Doe',
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $this->depositHandler->handle(new DepositCommand(
            accountId: (string) $account->getId(),
            amount: 10000,
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        // // temporaire
        // $entries = static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class)
        //     ->createQuery('SELECT e FROM App\Domain\Transaction\Entry e')
        //     ->getResult();
        // dump(count($entries));

        $transaction = $this->handler->handle(new WithdrawCommand(
            accountId: (string) $account->getId(),
            amount: 5000,
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $this->assertSame(TransactionStatus::Completed, $transaction->getStatus());
        $this->assertSame(TransactionType::Withdrawal, $transaction->getType());
        $this->assertSame(5000, $transaction->getAmount());
    }

    public function testWithdrawFailsWhenInsufficientFunds(): void
    {
        $apiClient = new ApiClient('Test Client', 'test-key-withdraw-fail');
        $this->apiClientRepository->save($apiClient);

        $account = $this->createAccountHandler->handle(new CreateAccountCommand(
            owner: 'John Doe',
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Insufficient funds.');

        $this->handler->handle(new WithdrawCommand(
            accountId: (string) $account->getId(),
            amount: 5000,
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));
    }
}
