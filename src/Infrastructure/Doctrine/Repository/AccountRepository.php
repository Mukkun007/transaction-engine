<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Account\Account;
use App\Domain\Account\AccountRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class AccountRepository implements AccountRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findById(Uuid $id): ?Account
    {
        return $this->em->getRepository(Account::class)->find($id);
    }

    public function findByReference(string $reference): ?Account
    {
        return $this->em->getRepository(Account::class)->findOneBy(['reference' => $reference]);
    }

    public function save(Account $account): void
    {
        $this->em->persist($account);
        $this->em->flush();
    }
}
