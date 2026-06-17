<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Idempotency\IdempotencyKey;
use App\Domain\Idempotency\IdempotencyRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class IdempotencyRepository implements IdempotencyRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByKeyAndClient(string $keyValue, string $apiClientId): ?IdempotencyKey
    {
        return $this->em->getRepository(IdempotencyKey::class)->findOneBy([
            'keyValue' => $keyValue,
            'apiClient' => $apiClientId,
        ]);
    }

    public function save(IdempotencyKey $key): void
    {
        $this->em->persist($key);
        $this->em->flush();
    }
}
