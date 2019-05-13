<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class KeycloakBearerAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var ClientRegistry
     */
    protected $clientRegistry;

    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
    }

    public function createToken(Request $request, $providerKey)
    {
        $token = $request->headers->get('Authorization');

        if (!$token) {
            throw new BadCredentialsException();
        }

        return new PreAuthenticatedToken(
            'anon.',
            trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $token)),
            $providerKey
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $user = $userProvider->loadUserByUsername($token->getCredentials());

        if (!$user) {
            throw new CustomUserMessageAuthenticationException(
                sprintf('Token does not exist.')
            );
        }

        return new PreAuthenticatedToken(
            $user,
            $token->getCredentials(),
            $providerKey,
            $user->getRoles()
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['error' => $exception->getMessage()], 403);
    }

    protected function getKeycloakClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('keycloak');
    }
}
