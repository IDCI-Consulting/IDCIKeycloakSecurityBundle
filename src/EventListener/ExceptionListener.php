<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\EventListener;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ExceptionListener
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof IdentityProviderException) {
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate(
                    'idci_keycloak_security_auth_connect',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ));
        }
    }
}
