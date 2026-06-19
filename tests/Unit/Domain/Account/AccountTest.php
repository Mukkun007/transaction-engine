<?php

namespace App\Tests\Unit\Domain\Account;

use App\Domain\Account\Account;
use App\Domain\Account\AccountStatus;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    public function testAccountIsCreatedWithActiveStatus(): void
    {
        $account = new Account('ACC-001', 'John Doe', 'EUR');

        $this->assertSame('ACC-001', $account->getReference());
        $this->assertSame('John Doe', $account->getOwner());
        $this->assertSame('EUR', $account->getCurrency());
        $this->assertSame(AccountStatus::Active, $account->getStatus());
    }

    public function testAccountCanBeFrozen(): void
    {
        $account = new Account('ACC-001', 'John Doe', 'EUR');
        $account->freeze();

        $this->assertSame(AccountStatus::Frozen, $account->getStatus());
    }

    public function testAccountCanBeClosed(): void
    {
        $account = new Account('ACC-001', 'John Doe', 'EUR');
        $account->close();

        $this->assertSame(AccountStatus::Closed, $account->getStatus());
    }
}
