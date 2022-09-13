<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\User;

use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Token\AccessToken;

class KeycloakBearerUser extends OAuthUser
{
    /**
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var bool
     */
    private $emailVerified;

    public function __toString(): string
    {
        return $this->getDisplayName();
    }

    public function setAccessToken(AccessToken $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getAccessToken(): ?AccessToken
    {
        return $this->accessToken;
    }

    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmailVerified(bool $emailVerified): self
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }
}
