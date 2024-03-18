<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\EventListener;

use IDCI\Bundle\KeycloakSecurityBundle\Security\User\KeycloakUser;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    private ClientRegistry $clientRegistry;

    private UrlGeneratorInterface $urlGenerator;

    private TokenStorageInterface $tokenStorage;

    private string $defaultTargetRouteName;

    public function __construct(
        ClientRegistry $clientRegistry,
        UrlGeneratorInterface $urlGenerator,
        TokenStorageInterface $tokenStorage,
        string $defaultTargetRouteName
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->urlGenerator = $urlGenerator;
        $this->tokenStorage = $tokenStorage;
        $this->defaultTargetRouteName = $defaultTargetRouteName;
    }

    public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event): void
    {
        if (null === $event->getToken()?->getUser()) {
            return;
        }

        $user = $event->getToken()->getUser();
        if (!$user instanceof KeycloakUser) {
            return;
        }

        $oAuth2Provider = $this->clientRegistry->getClient('keycloak')->getOAuth2Provider();
        $logoutUrl = $oAuth2Provider->getLogoutUrl([
            'state' => $user->getAccessToken()->getValues()['session_state'],
            'access_token' => $user->getAccessToken(),
            'redirect_uri' => $this->urlGenerator->generate($this->defaultTargetRouteName, [], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);

        $this->tokenStorage->setToken(null);
        $event->getRequest()->getSession()->invalidate();

        $event->setResponse(new RedirectResponse($logoutUrl));
    }
}