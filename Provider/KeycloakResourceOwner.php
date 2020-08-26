<?php

namespace NTI\KeycloakSecurityBundle\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class KeycloakResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @var AccessToken
     */
    protected $token;

    public function __construct(array $response = [], AccessToken $token)
    {
        $this->response = $response;
        $this->token = $token;
    }

    public function getId(): ?string
    {
        return isset($this->response['sub']) ? $this->response['sub'] : null;
    }

    public function getPreferredUsername(): ?string
    {
        return isset($this->response['preferred_username']) ? $this->response['preferred_username'] : null;
    }

    public function getEmail(): ?string
    {
        return isset($this->response['email']) ? $this->response['email'] : null;
    }

    public function getName(): ?string
    {
        return isset($this->response['name']) ? $this->response['name'] : null;
    }

    public function getFirstName(): ?string
    {
        return isset($this->response['given_name']) ? $this->response['given_name'] : null;
    }

    public function getLastName(): ?string
    {
        return isset($this->response['family_name']) ? $this->response['family_name'] : null;
    }

    public function getLocale(): ?string
    {
        return isset($this->response['locale']) ? $this->response['locale'] : null;
    }

    public function getRoles(): array
    {
        return isset($this->response['roles']) ? $this->response['roles'] : [];
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
