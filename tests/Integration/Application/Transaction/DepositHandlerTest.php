<?php

namespace App\Tests\Integration\Application\Transaction;

use App\Application\Account\CreateAccount\CreateAccountCommand;
use App\Application\Account\CreateAccount\CreateAccountHandler;
use App\Application\Transaction\Deposit\DepositCommand;
use App\Application\Transaction\Deposit\DepositHandler;
use App\Domain\Auth\ApiClient;
use App\Domain\Auth\ApiClientRepositoryInterface;
use App\Domain\Transaction\TransactionStatus;
use App\Domain\Transaction\TransactionType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DepositHandlerTest extends KernelTestCase
{
    private DepositHandler $handler;
    private CreateAccountHandler $createAccountHandler;
    private ApiClientRepositoryInterface $apiClientRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(DepositHandler::class);
        $this->createAccountHandler = $container->get(CreateAccountHandler::class);
        $this->apiClientRepository = $container->get(ApiClientRepositoryInterface::class);
    }

    public function testDepositCreatesCompletedTransaction(): void
    {
        $apiClient = new ApiClient('Test Client', 'test-key-deposit');
        $this->apiClientRepository->save($apiClient);

        $account = $this->createAccountHandler->handle(new CreateAccountCommand(
            owner: 'John Doe',
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $transaction = $this->handler->handle(new DepositCommand(
            accountId: (string) $account->getId(),
            amount: 5000,
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        ));

        $this->assertSame(TransactionStatus::Completed, $transaction->getStatus());
        $this->assertSame(TransactionType::Deposit, $transaction->getType());
        $this->assertSame(5000, $transaction->getAmount());
    }
}
