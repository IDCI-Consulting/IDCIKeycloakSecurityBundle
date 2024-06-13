<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class KeycloakController extends AbstractController
{
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('keycloak')->redirect();
    }

    public function connectCheck(Request $request, string $defaultTargetRouteName): RedirectResponse
    {
        $loginReferrer = null;
        if ($request->hasSession()) {
            $loginReferrer = $request->getSession()->remove('loginReferrer');
        }

        return $loginReferrer ? $this->redirect($loginReferrer) : $this->redirectToRoute($defaultTargetRouteName);
    }

    public function logout(Request $request, string $defaultTargetRouteName): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl($defaultTargetRouteName));
    }

    public function account(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $this->redirect($clientRegistry->getClient('keycloak')->getOAuth2Provider()->getResourceOwnerManageAccountUrl());
    }
}
