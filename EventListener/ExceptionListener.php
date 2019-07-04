<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\EventListener;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ExceptionListener
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

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
