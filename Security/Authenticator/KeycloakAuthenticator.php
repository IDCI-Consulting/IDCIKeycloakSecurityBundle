<?php

namespace NTI\KeycloakSecurityBundle\Security\Authenticator;

use NTI\KeycloakSecurityBundle\Security\User\KeycloakUser;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KeycloakAuthenticator extends SocialAuthenticator
{
    /**
     * @var ClientRegistry
     */
    protected $clientRegistry;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ClientRegistry $clientRegistry, UrlGeneratorInterface $urlGenerator, ContainerInterface $container)
    {
        $this->clientRegistry = $clientRegistry;
        $this->urlGenerator = $urlGenerator;
        $this->container = $container;
    }

    public function supports(Request $request)
    {
        return 'nti_security_auth_connect_check_keycloak' === $request->attributes->get('_route');
    }

    public function getCredentials(Request $request): ?AccessToken
    {
        if (!$this->supports($request)) {
            return null;
        }

        return $this->fetchAccessToken($this->getKeycloakClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider): KeycloakUser
    {
        return $userProvider->loadUserByUsername($credentials);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // Get local user & refresh its last login date
        $token = $this->container->get('security.token_storage')->getToken();
        $this->container->get('app.user')->refreshLastLogin($token->getUser()->getLocalUser());
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        return new RedirectResponse(
            $this->urlGenerator->generate('nti_security_auth_connect_keycloak'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    protected function getKeycloakClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('keycloak');
    }
}
