<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class KeycloakResourceOwner implements ResourceOwnerInterface
{
    protected array $response;
    protected AccessToken $token;

    public function __construct(array $response, AccessToken $token)
    {
        $this->response = $response;
        $this->token = $token;
    }

    public function getId(): ?string
    {
        return $this->response['sub'] ?? null;
    }

    public function getPreferredUsername(): ?string
    {
        return $this->response['preferred_username'] ?? null;
    }

    public function getEmail(): ?string
    {
        return $this->response['email'] ?? null;
    }

    public function getName(): ?string
    {
        return $this->response['name'] ?? null;
    }

    public function getFirstName(): ?string
    {
        return $this->response['given_name'] ?? null;
    }

    public function getLastName(): ?string
    {
        return $this->response['family_name'] ?? null;
    }

    public function getLocale(): ?string
    {
        return $this->response['locale'] ?? null;
    }

    public function getResourceAccess(): array
    {
        return $this->response['resource_access'] ?? [];
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
