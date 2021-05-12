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

    /**
     * @var mixed
     */
    protected $sslVerification;

    public function __construct(ClientRegistry $clientRegistry, $sslVerification)
    {
        $this->clientRegistry = $clientRegistry;
        $this->sslVerification = $sslVerification;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($accessToken): UserInterface
    {
        $provider = $this->getKeycloakClient()->getOAuth2Provider();

        if (!$provider instanceof Keycloak) {
            throw new \RuntimeException(
                sprintf('The OAuth2 client provider must be an instance of %s', Keycloak::class)
            );
        }

        $response = (new Client())->request('POST', $provider->getTokenIntrospectionUrl(), [
            'auth' => [$provider->getClientId(), $provider->getClientSecret()],
            'form_params' => [
                'token' => $accessToken,
            ],
            'verify' => $this->sslVerification,
        ]);

        $jwt = json_decode($response->getBody(), true);

        if (!$jwt['active']) {
            throw new \UnexpectedValueException('The token does not exist or is not valid anymore');
        }

        if (!isset($jwt['resource_access'][$provider->getClientId()])) {
            throw new \UnexpectedValueException(sprintf(
                'The token does not have the necessary permissions. Configure roles in the client \'%s\' of the realm \'%s\' and associate them with the user \'%s\'',
                $provider->getClientId(),
                $provider->realm,
                $jwt['username']
            ));
        }

        return new KeycloakBearerUser(
            $jwt['username'],
            $jwt['resource_access'][$provider->getClientId()]['roles'],
            $accessToken
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
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
