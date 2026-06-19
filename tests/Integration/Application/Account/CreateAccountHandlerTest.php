<?php

namespace App\Tests\Integration\Application\Account;

use App\Application\Account\CreateAccount\CreateAccountCommand;
use App\Application\Account\CreateAccount\CreateAccountHandler;
use App\Domain\Account\AccountStatus;
use App\Domain\Auth\ApiClient;
use App\Domain\Auth\ApiClientRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CreateAccountHandlerTest extends KernelTestCase
{
    private CreateAccountHandler $handler;
    private ApiClientRepositoryInterface $apiClientRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(CreateAccountHandler::class);
        $this->apiClientRepository = $container->get(ApiClientRepositoryInterface::class);
    }

    public function testCreateAccountCreatesAccountWithCorrectData(): void
    {
        $apiClient = new ApiClient('Test Client', 'test-key-integration');
        $this->apiClientRepository->save($apiClient);

        $command = new CreateAccountCommand(
            owner: 'Jane Doe',
            currency: 'EUR',
            apiClientId: (string) $apiClient->getId(),
        );

        $account = $this->handler->handle($command);

        $this->assertSame('Jane Doe', $account->getOwner());
        $this->assertSame('EUR', $account->getCurrency());
        $this->assertSame(AccountStatus::Active, $account->getStatus());
        $this->assertStringStartsWith('ACC-', $account->getReference());
    }
}
