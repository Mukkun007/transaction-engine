<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Auth\ApiClient;
use App\Domain\Auth\ApiClientRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class ApiClientRepository implements ApiClientRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findById(Uuid $id): ?ApiClient
    {
        return $this->em->getRepository(ApiClient::class)->find($id);
    }

    public function findByApiKey(string $apiKey): ?ApiClient
    {
        return $this->em->getRepository(ApiClient::class)->findOneBy(['apiKey' => $apiKey]);
    }

    public function save(ApiClient $apiClient): void
    {
        $this->em->persist($apiClient);
        $this->em->flush();
    }
}
