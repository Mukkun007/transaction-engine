<?php

namespace App\Infrastructure\Security;

use App\Domain\Auth\ApiClient;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiClientUser implements UserInterface
{
    public function __construct(private ApiClient $apiClient) {}

    public function getApiClient(): ApiClient
    {
        return $this->apiClient;
    }

    public function getRoles(): array
    {
        return ['ROLE_API_CLIENT'];
    }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        return (string) $this->apiClient->getId();
    }
}
