<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class KeycloakBearerAuthenticator extends AbstractGuardAuthenticator
{
    public function supports(Request $request)
    {
        return $request->headers->has('Authorization');
    }

    public function getCredentials(Request $request)
    {
        return [
            'token' => $request->headers->get('Authorization'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = $credentials['token'];

        if (!$token) {
            throw new BadCredentialsException('Token is not present in the request headers');
        }

        try {
            $user = $userProvider->loadUserByUsername($this->formatToken($token));
        } catch (\Exception $e) {
            throw new BadCredentialsException(sprintf('Error when introspecting the token: %s', $e->getMessage()));
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }

    private function formatToken(string $token): string
    {
        return trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $token));
    }
}
