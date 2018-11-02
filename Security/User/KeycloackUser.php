<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\User;

use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Security\Core\User\UserInterface;

class KeycloackUser extends OAuthUser
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
    private $accountUrl;

    /**
     * @var string
     */
    private $preferredLanguage;

    /**
     * @var string
     */
    private $accessibleManagers;

    public function __construct(
        string $username,
        array $roles,
        AccessToken $accessToken,
        string $id,
        string $email,
        string $displayName,
        string $accountUrl,
        string $preferredLanguage = 'en',
        array $accessibleManagers = []
    ) {
        $this->accessToken = $accessToken;
        $this->id = $id;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->accountUrl = $accountUrl;
        $this->preferredLanguage = $preferredLanguage;
        $this->accessibleManagers = $accessibleManagers;

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

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function getAccountUrl(): ?string
    {
        return $this->accountUrl;
    }

    public function getPreferredLanguage(): ?string
    {
        return $this->preferredLanguage;
    }

    public function getAccessibleManagers(): ?array
    {
        return $this->accessibleManagers;
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
