<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\User;

use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Security\Core\User\UserInterface;

class KeycloakUser extends OAuthUser
{
    private AccessToken $accessToken;

    private string $id;

    private ?string $email;

    private ?string $displayName;

    private ?string $firstName;

    private ?string $lastName;

    private string $accountUrl;

    private ?string $preferredLanguage;

    private array $resources;

    public function __construct(
        string $username,
        array $roles,
        AccessToken $accessToken,
        string $id,
        ?string $email,
        ?string $displayName,
        ?string $firstName,
        ?string $lastName,
        string $accountUrl,
        ?string $preferredLanguage = 'en',
        array $resources = []
    ) {
        $this->accessToken = $accessToken;
        $this->id = $id;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->accountUrl = $accountUrl;
        $this->preferredLanguage = $preferredLanguage;
        $this->resources = $resources;

        parent::__construct($username, $roles);
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }

    public function getAccessToken(): ?AccessToken
    {
        return $this->accessToken;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getAccountUrl(): ?string
    {
        return $this->accountUrl;
    }

    public function getPreferredLanguage(): ?string
    {
        return $this->preferredLanguage;
    }

    public function getResources(): array
    {
        return $this->resources;
    }

    public function getResource(string $name): mixed
    {
        return array_key_exists($name, $this->resources) ? $this->resources[$name] : null;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($this->getId() !== $user->getId()) {
            return false;
        }

        return true;
    }
}
