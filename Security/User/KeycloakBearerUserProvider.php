<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\User;

use GuzzleHttp\Client;
use IDCI\Bundle\KeycloakSecurityBundle\Provider\Keycloak;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class KeycloakBearerUserProvider extends OAuthUserProvider
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
    public function loadUserByUsername($accessToken): ?KeycloakBearerUser
    {
        $provider = $this->getKeycloakClient()->getOAuth2Provider();

        if (!$provider instanceof Keycloak) {
            throw new \RuntimeException(
                sprintf('The OAuth2 client provider must be an instance of %s', Keycloak::class)
            );
        }

        try {
            $response = (new Client())->request('POST', $provider->getTokenIntrospectionUrl(), [
                'auth' => [$provider->getClientId(), $provider->getClientSecret()],
                'form_params' => [
                    'token' => $accessToken,
                ],
            ]);
        } catch (\Exception $e) {
            return null;
        }

        $jwt = json_decode($response->getBody(), true);

        if (!$jwt['active'] || !isset($jwt['resource_access'][$provider->getClientId()])) {
            return null;
        }

        return new KeycloakBearerUser(
            $jwt['clientId'],
            $jwt['resource_access'][$provider->getClientId()]['roles'],
            $accessToken
        );
    }

    public function refreshUser(UserInterface $user): ?KeycloakBearerUser
    {
        if (!$user instanceof KeycloakBearerUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user = $this->loadUserByUsername($user->getAccessToken());

        if (!$user) {
            throw new UsernameNotFoundException();
        }

        return $user;
    }

    public function supportsClass($class): bool
    {
        return KeycloakBearerUser::class === $class;
    }

    protected function getKeycloakClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('keycloak');
    }
}
