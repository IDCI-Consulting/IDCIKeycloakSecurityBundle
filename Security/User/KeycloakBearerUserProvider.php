<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\User;

use IDCI\Bundle\KeycloakSecurityBundle\Provider\KeycloakProvider;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KeycloakBearerUserProvider extends OAuthUserProvider implements KeycloakBearerUserProviderInterface
{
    protected ClientRegistry $clientRegistry;

    protected HttpClientInterface $httpClient;

    protected mixed $sslVerification;

    public function __construct(ClientRegistry $clientRegistry, HttpClientInterface $httpClient, mixed $sslVerification)
    {
        parent::__construct();
        $this->clientRegistry = $clientRegistry;
        $this->httpClient = $httpClient;
        $this->sslVerification = $sslVerification;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdentifier(string $accessToken): UserInterface
    {
        $provider = $this->getKeycloakClient()->getOAuth2Provider();

        if (!$provider instanceof KeycloakProvider) {
            throw new \RuntimeException(sprintf('The OAuth2 client provider must be an instance of %s', KeycloakProvider::class));
        }

        $response = $this->httpClient->request(Request::METHOD_POST, $provider->getTokenIntrospectionUrl(), [
            'body' => [
                'client_id' => $provider->getClientId(),
                'client_secret' => $provider->getClientSecret(),
                'grant_type' => 'client_credentials',
                'token' => $accessToken,
            ],
            'verify_host' => $this->sslVerification,
            'verify_peer' => $this->sslVerification,
        ]);

        $jwt = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!$jwt['active']) {
            throw new TokenNotFoundException('The token does not exist or is not valid anymore');
        }

        if (!isset($jwt['resource_access'][$provider->getClientId()])) {
            throw new AccessDeniedException(sprintf(
                'The token does not have the necessary permissions. Configure roles in the client \'%s\' of the realm \'%s\' and associate them with the user \'%s\'',
                $provider->getClientId(),
                $provider->realm,
                $jwt['username']
            ));
        }

        return (new KeycloakBearerUser($jwt['username'], $jwt['resource_access'][$provider->getClientId()]['roles']))
            ->setAccessToken($accessToken)
            ->setClientId($jwt['client_id'])
            ->setFirstName($jwt['given_name'] ?? null)
            ->setLastName($jwt['family_name'] ?? null)
            ->setEmail($jwt['email'] ?? null)
            ->setEmailVerified($jwt['email_verified'])
        ;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof KeycloakBearerUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user = $this->loadUserByIdentifier($user->getAccessToken());

        if (!$user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function supportsClass($class): bool
    {
        return KeycloakBearerUser::class === $class;
    }

    protected function getKeycloakClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('keycloak');
    }
}
