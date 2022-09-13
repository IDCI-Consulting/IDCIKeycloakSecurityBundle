<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator;

use IDCI\Bundle\KeycloakSecurityBundle\Security\User\KeycloakBearerUserProvider;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * TODO migrate to AccessTokenHandlerInterface in Symfony 6.2+
 * @see https://github.com/symfony/symfony-docs/pull/16819
 * @see https://github.com/symfony/symfony/pull/46428
 */
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
        $apiToken = self::cleanToken($request->headers->get('Authorization'));
        if (null === $apiToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $userProvider = $this->userProvider;

        return new SelfValidatingPassport(
            new UserBadge(
                $apiToken,
                function() use ($apiToken, $userProvider) {
                    return $userProvider->loadUserByIdentifier($apiToken);
                }
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new JsonResponse(['message' => $message], Response::HTTP_UNAUTHORIZED);
    }

    private static function cleanToken(string $token): string
    {
        return trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $token));
    }
}
