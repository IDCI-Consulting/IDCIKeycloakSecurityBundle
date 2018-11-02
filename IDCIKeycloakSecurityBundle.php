<?php

namespace IDCI\Bundle\KeycloakSecurityBundle;

use IDCI\Bundle\KeycloakSecurityBundle\DependencyInjection\IDCIKeycloakSecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IDCIKeycloakSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    public function getContainerExtension()
    {
        return new IDCIKeycloakSecurityExtension();
    }
}
