<?php

namespace NTI\KeycloakSecurityBundle\Security\User;

use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Security\Core\User\UserInterface;

class KeycloakUser extends OAuthUser
{
    /**
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @var string
     */
    private $id;

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
     * @var string
     */
    private $accountUrl;

    /**
     * @var string
     */
    private $preferredLanguage;

    public function __construct(
        string $username,
        array $roles,
        AccessToken $accessToken,
        string $id,
        ?string $email = null,
        ?string $displayName = null,
        ?string $firstName = null,
        ?string $lastName = null,
        string $accountUrl,
        ?string $preferredLanguage = 'en'
    ) {
        $this->accessToken = $accessToken;
        $this->id = $id;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->accountUrl = $accountUrl;
        $this->preferredLanguage = $preferredLanguage;

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
