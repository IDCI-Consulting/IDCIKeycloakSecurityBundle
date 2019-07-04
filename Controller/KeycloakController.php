<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Controller;

use IDCI\Bundle\KeycloakSecurityBundle\Security\User\KeycloakUser;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class KeycloakController extends AbstractController
{
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('keycloak')->redirect();
    }

    public function connectCheckAction(Request $request, string $defaultTargetPath)
    {
        return $this->redirectToRoute($defaultTargetPath);
    }

    public function logoutAction(
        Request $request,
        ClientRegistry $clientRegistry,
        TokenStorageInterface $tokenStorage,
        UrlGeneratorInterface $urlGenerator,
        string $defaultTargetPath
    ) {
        $token = $tokenStorage->getToken();
        $user = $token->getUser();

        if (!$user instanceof KeycloakUser) {
            throw new \RuntimeException('The user must be an instance of KeycloakUser');
        }

        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        $values = $user->getAccessToken()->getValues();
        $oAuth2Provider = $clientRegistry->getClient('keycloak')->getOAuth2Provider();

        return new RedirectResponse($oAuth2Provider->getLogoutUrl([
            'state' => $values['session_state'],
            'redirect_uri' => $urlGenerator->generate($defaultTargetPath, [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]));
    }
}
