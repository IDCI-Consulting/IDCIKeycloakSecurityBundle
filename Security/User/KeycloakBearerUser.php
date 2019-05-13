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

    public function __construct(string $username, array $roles, string $accessToken)
    {
        $this->accessToken = $accessToken;

        parent::__construct($username, $roles);
    }

    public function __toString(): string
    {
        return $this->getUsername();
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }
}
