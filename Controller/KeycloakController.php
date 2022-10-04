<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class KeycloakController extends AbstractController
{
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('keycloak')->redirect();
    }

    public function connectCheckAction(Request $request, string $defaultTargetPath)
    {
        $loginReferrer = null;
        if ($request->hasSession()) {
            $loginReferrer = $request->getSession()->remove('loginReferrer');
        }

        return $loginReferrer ? $this->redirect($loginReferrer) : $this->redirectToRoute($defaultTargetPath);
    }

    public function logoutAction(Request $request, string $defaultTargetPath)
    {
        return new RedirectResponse($this->generateUrl($defaultTargetPath));
    }
}
