<?php

namespace NTI\KeycloakSecurityBundle\Security\User;

use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Token\AccessToken;

class KeycloakBearerUser extends OAuthUser
{
    /**
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @var
     */
    private $localUser;

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
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $fromClient;

    public function __construct(
        string $username,
        array $roles,
        $localUser,
        AccessToken $accessToken,
        string $id,
        ?string $email = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $fromClient
    ) {
        $this->localUser = $localUser;
        $this->accessToken = $accessToken;
        $this->id = $id;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->fromClient = $fromClient;

        parent::__construct($username, $roles);
    }

    public function __toString(): string
    {
        return $this->getEmail();
    }

    public function getLocalUser()
    {
        return $this->localUser;
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

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Returns true if the user has the role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        $roles = $this->getRoles();

        foreach ($roles as $key => $userRole) {
            if($userRole == $role)
                return true;
        }

        return false;
    }

    public function getFromClient()
    {
        return $this->fromClient;
    }
}
