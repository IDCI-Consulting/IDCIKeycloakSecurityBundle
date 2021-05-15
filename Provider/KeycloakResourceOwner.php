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

    public function getRoles($clientId): array
    {
        if(isset($this->response['roles']) && isset($this->response['roles'][$clientId])){
            if(isset($this->response['denied_roles']) && isset($this->response['denied_roles'][$clientId]) && is_array($this->response['denied_roles'])){
                $roles = array(); // Remove denied roles
                foreach($this->response['roles'][$clientId] as $val) $roles[$val] = 1;
                foreach($this->response['denied_roles'][$clientId] as $val) unset($roles[$val]);
                return array_values(array_keys($roles));
            }
            
            return array_values($this->response['roles'][$clientId]);
        } else {
            return [];
        }
    }

    public function getCreatedOn(): ?\DateTime
    {
        $date = new \DateTime();
        return isset($this->response['created_on']) ? $date->setTimestamp($this->response['created_on']/1000) : null;
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
