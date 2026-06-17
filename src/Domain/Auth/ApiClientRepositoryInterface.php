<?php

namespace App\Domain\Auth;

use Symfony\Component\Uid\Uuid;

interface ApiClientRepositoryInterface
{
    public function findById(Uuid $id): ?ApiClient;
    public function findByApiKey(string $apiKey): ?ApiClient;
    public function save(ApiClient $apiClient): void;
}
