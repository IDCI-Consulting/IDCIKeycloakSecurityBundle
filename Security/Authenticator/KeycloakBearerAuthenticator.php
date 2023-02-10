<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator;

use IDCI\Bundle\KeycloakSecurityBundle\Security\User\KeycloakBearerUserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class KeycloakBearerAuthenticator extends AbstractAuthenticator
{
    protected KeycloakBearerUserProvider $userProvider;

    public function __construct(KeycloakBearerUserProvider $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader) {
            throw new AuthenticationException('"Authorization" is missing in the request headers');
        }

        try {
            return new SelfValidatingPassport(
                new UserBadge($authorizationHeader, function() use ($authorizationHeader) {
                    return $this->userProvider->loadUserByUsername(self::getBearerTokenFromHeader($authorizationHeader));
                })
            );
        } catch (\Exception $e) {
            throw new UserNotFoundException(sprintf('Error during token introspection: %s', $e->getMessage()));
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
    }

    public static function getBearerTokenFromHeader(string $token): string
    {
        return trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $token));
    }
}
