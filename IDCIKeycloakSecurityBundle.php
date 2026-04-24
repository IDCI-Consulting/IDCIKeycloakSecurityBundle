<?php

namespace IDCI\Bundle\KeycloakSecurityBundle;

use IDCI\Bundle\KeycloakSecurityBundle\DependencyInjection\IDCIKeycloakSecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IDCIKeycloakSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new IDCIKeycloakSecurityExtension();
    }
}
