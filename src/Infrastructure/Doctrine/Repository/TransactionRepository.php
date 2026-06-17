<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\TransactionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findById(Uuid $id): ?Transaction
    {
        return $this->em->getRepository(Transaction::class)->find($id);
    }

    public function findByReference(string $reference): ?Transaction
    {
        return $this->em->getRepository(Transaction::class)->findOneBy(['reference' => $reference]);
    }

    public function save(Transaction $transaction): void
    {
        $this->em->persist($transaction);
        $this->em->flush();
    }
}
