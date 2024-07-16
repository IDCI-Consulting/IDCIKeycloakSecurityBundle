<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\User;

use IDCI\Bundle\KeycloakSecurityBundle\Provider\KeycloakProvider;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUserProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class KeycloakUserProvider extends OAuthUserProvider implements KeycloakUserProviderInterface
{
    protected ClientRegistry $clientRegistry;

    protected LoggerInterface $logger;

    public function __construct(ClientRegistry $clientRegistry, LoggerInterface $logger)
    {
        parent::__construct();
        $this->clientRegistry = $clientRegistry;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdentifier($identifier): UserInterface
    {
        if (!$identifier instanceof AccessToken) {
            throw new \LogicException('Could not load a KeycloakUser without an AccessToken.');
        }

        $provider = $this->getKeycloakClient()->getOAuth2Provider();
        try {
            $resourceOwner = $this->getKeycloakClient()->fetchUserFromToken($identifier);
        } catch (\UnexpectedValueException $e) {
            $this->logger->warning($e->getMessage());
            $this->logger->warning('User should have been disconnected from Keycloak server');

            throw new UserNotFoundException();
        }

        if (!$provider instanceof KeycloakProvider) {
            throw new \RuntimeException(
                sprintf('The OAuth2 client provider must be an instance of %s', KeycloakProvider::class)
            );
        }

        $roles = [];
        // @deprecated: For old keycloak version, keep retrieve the roles directly from the resource owner
        if (!empty($resourceOwner->getRoles())) {
            $roles = array_map(
                function ($role) {
                    return strtoupper($role);
                },
                $resourceOwner->getRoles()
            );
        }

        if (isset($resourceOwner->getResourceAccess()[$provider->getClientId()])) {
            $roles = array_map(
                function ($role) {
                    return strtoupper($role);
                },
                $resourceOwner->getResourceAccess()[$provider->getClientId()]['roles']
            );
        }

        return new KeycloakUser(
            $resourceOwner->getPreferredUsername(),
            $roles,
            $identifier,
            $resourceOwner->getId(),
            $resourceOwner->getEmail(),
            $resourceOwner->getName(),
            $resourceOwner->getFirstName(),
            $resourceOwner->getLastName(),
            $resourceOwner->getLocale(),
            $resourceOwner->toArray()
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

        $user = $this->loadUserByIdentifier($accessToken);
        if (null === $user) {
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
