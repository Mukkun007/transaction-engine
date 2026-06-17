<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Audit\AuditLog;
use App\Domain\Audit\AuditLogRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(AuditLog $log): void
    {
        $this->em->persist($log);
        $this->em->flush();
    }
}
