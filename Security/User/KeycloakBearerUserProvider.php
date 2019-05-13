<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\User;

use GuzzleHttp\Client;
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

        if (!$jwt['active']) {
            return null;
        }

        $roles = array_merge(
            $jwt['realm_access']['roles'],
            $jwt['resource_access'][$provider->getClientId()]['roles']
        );

        return new KeycloakBearerUser(
            $jwt['clientId'],
            $roles,
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
