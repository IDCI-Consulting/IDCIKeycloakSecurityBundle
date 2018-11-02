<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Controller;

use IDCI\Bundle\KeycloakSecurityBundle\Security\User\KeycloakUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class KeycloakController extends Controller
{
    public function connectAction()
    {
        return $this->getKeycloakClient()->redirect();
    }

    public function connectCheckAction(Request $request)
    {
        $routeName = $this->container->getParameter('idci_keycloak_security.default_target_path');

        return new RedirectResponse($this->container->get('router')->generate($routeName));
    }

    public function logoutAction(Request $request)
    {
        $token = $this->container->get('security.token_storage')->getToken();
        $user = $token->getUser();

        if (!$user instanceof KeycloakUser) {
            throw new \RuntimeException('The user must be an instance of KeycloakUser');
        }

        $this->container->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        $routeName = $this->container->getParameter('idci_keycloak_security.default_target_path');
        $values = $user->getAccessToken()->getValues();
        $oAuth2Provider = $this->getKeycloakClient()->getOAuth2Provider();

        return new RedirectResponse($oAuth2Provider->getLogoutUrl([
            'state' => $values['session_state'],
            'redirect_uri' => $this->container->get('router')->generate($routeName, [], Router::ABSOLUTE_URL),
        ]));
    }

    protected function getKeycloakClient()
    {
        return $this->container->get('knpu.oauth2.registry')->getClient('keycloak');
    }
}
