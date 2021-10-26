<?php

declare(strict_types=1);

namespace IDCI\Bundle\KeycloakSecurityBundle\EventListener;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ExceptionListener
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof IdentityProviderException) {
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate(
                    'idci_security_auth_connect_keycloak',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ));
        }
    }
}
