<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator;

use IDCI\Bundle\KeycloakSecurityBundle\Security\User\KeycloakUserProvider;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class KeycloakAuthenticator extends OAuth2Authenticator
{
    public function __construct(private readonly ClientRegistry $clientRegistry, private readonly KeycloakUserProvider $userProvider)
    {
    }

    public function supports(Request $request): ?bool
    {
        return 'idci_security_auth_connect_check_keycloak' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->getKeycloakClient();
        $accessToken = $this->fetchAccessToken($client);
        if (null === $accessToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No access token provided');
        }

        $userProvider = $this->userProvider;

        return new SelfValidatingPassport(
            new UserBadge(
                $accessToken->getToken(),
                function() use ($accessToken, $userProvider) {
                    return $userProvider->loadUserByIdentifier($accessToken);
                }
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    protected function getKeycloakClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('keycloak');
    }
}
