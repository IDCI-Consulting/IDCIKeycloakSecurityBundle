<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator;

use IDCI\Bundle\KeycloakSecurityBundle\Security\User\KeycloakBearerUserProvider;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class KeycloakBearerAuthenticator extends OAuth2Authenticator
{
    public function __construct(private readonly KeycloakBearerUserProvider $userProvider)
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $accessToken = self::cleanToken($request->headers->get('Authorization'));
        if (null === $accessToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            //throw new CustomUserMessageAuthenticationException('No access token provided');
            throw new BadCredentialsException('Token is missing in the request headers');
        }

        $userProvider = $this->userProvider;

        return new SelfValidatingPassport(
            new UserBadge(
                $accessToken,
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

        return new JsonResponse(['error' => $message], Response::HTTP_FORBIDDEN);
    }

    private static function cleanToken(string $token): string
    {
        return trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $token));
    }
}
