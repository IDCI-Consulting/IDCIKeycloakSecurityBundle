<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\EventListener;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof IdentityProviderException) {
            $event->setResponse(new RedirectResponse(
                $this->router->generate('idci_security_auth_connect_keycloak', [], Router::ABSOLUTE_URL)
            ));
        }
    }
}
