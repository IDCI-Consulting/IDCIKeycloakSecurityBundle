<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\User;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUserProvider;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class KeycloakUserProvider extends OAuthUserProvider
{
    /**
     * @var ClientRegistry
     */
    protected $clientRegistry;

    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($accessToken): KeycloakUser
    {
        if (!$accessToken instanceof AccessToken) {
            throw new \LogicException('Could not load a KeycloakUser without an AccessToken.');
        }

        $keycloakUser = $this->getKeycloakClient()->fetchUserFromToken($accessToken);

        $roles = array_map(
            function ($role) {
                return strtoupper($role);
            },
            $keycloakUser->getRoles()
        );


        return new KeycloakUser(
            $keycloakUser->getPreferredUsername(),
            $roles,
            $accessToken,
            $keycloakUser->getId(),
            $keycloakUser->getEmail(),
            $keycloakUser->getName(),
            $keycloakUser->getFirstName(),
            $keycloakUser->getLastName(),
            $this->getKeycloakClient()->getOAuth2Provider()->getResourceOwnerManageAccountUrl(),
            $keycloakUser->getLocale()
        );
    }

    public function refreshUser(UserInterface $user): keycloakUser
    {
        if (!$user instanceof keycloakUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $accessToken = $user->getAccessToken();

        if ($accessToken->hasExpired()) {
            $accessToken = $this->getKeycloakClient()->getOAuth2Provider()->getAccessToken(
                'refresh_token',
                [
                    'refresh_token' => $accessToken->getRefreshToken(),
                ]
            );
        }

        return $this->loadUserByUsername($accessToken);
    }

    public function supportsClass($class): bool
    {
        return keycloakUser::class === $class;
    }

    protected function getKeycloakClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('keycloak');
    }
}
