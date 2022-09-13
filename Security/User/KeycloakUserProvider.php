<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\User;

use IDCI\Bundle\KeycloakSecurityBundle\Provider\KeycloakProvider;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUserProvider;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class KeycloakUserProvider extends OAuthUserProvider
{
    public function __construct(private readonly ClientRegistry $clientRegistry)
    {
    }

    public function loadUserByIdentifier(string|AccessToken $identifier): UserInterface
    {
        $accessToken = $identifier;
        if (!$accessToken instanceof AccessToken) {
            throw new \LogicException('Could not load a KeycloakUser without an AccessToken.');
        }

        $provider = $this->getKeycloakClient()->getOAuth2Provider();
        $keycloakUser = $this->getKeycloakClient()->fetchUserFromToken($accessToken);

        if (!$provider instanceof KeycloakProvider) {
            throw new \RuntimeException(
                sprintf('The OAuth2 client provider must be an instance of %s', KeycloakProvider::class)
            );
        }

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
            $provider->getResourceOwnerManageAccountUrl(),
            $keycloakUser->getLocale()
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof KeycloakUser) {
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

        $user = $this->loadUserByUsername($accessToken);
        if (!$user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function supportsClass($class): bool
    {
        return KeycloakUser::class === $class;
    }

    protected function getKeycloakClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('keycloak');
    }
}
