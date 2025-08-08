<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Security\EntryPoint;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        if ($request->hasSession()) {
            // store URI for later redirect
            $request->getSession()->set('loginReferrer', $request->getUri());
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('idci_keycloak_security_auth_connect'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}