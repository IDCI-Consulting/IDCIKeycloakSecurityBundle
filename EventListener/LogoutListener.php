<?php
declare(strict_types=1);

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
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly string $defaultTargetPath,
    ) {
    }

    public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {
        $user = $event->getToken()?->getUser();
        if (null === $user) {
            return;
        }

        if (!$user instanceof KeycloakUser) {
            throw new \RuntimeException('The user must be an instance of KeycloakUser');
        }

        $token = $user->getAccessToken();

        $this->tokenStorage->setToken(null);
        $event->getRequest()->getSession()->invalidate();

        $values = $token->getValues();
        $oAuth2Provider = $this->clientRegistry->getClient('keycloak')->getOAuth2Provider();

        $logoutUrl = $oAuth2Provider->getLogoutUrl([
            'state' => $values['session_state'],
            'access_token' => $token,
            'redirect_uri' => $this->urlGenerator->generate($this->defaultTargetPath, [], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);

        $event->setResponse(new RedirectResponse($logoutUrl));
    }
}
