<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Outbox\OutboxMessage;
use App\Domain\Outbox\OutboxRepositoryInterface;
use App\Domain\Outbox\OutboxStatus;
use Doctrine\ORM\EntityManagerInterface;

class OutboxRepository implements OutboxRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(OutboxMessage $message): void
    {
        $this->em->persist($message);
        $this->em->flush();
    }

    public function findPending(): array
    {
        return $this->em->getRepository(OutboxMessage::class)->findBy([
            'status' => OutboxStatus::Pending,
        ]);
    }
}
