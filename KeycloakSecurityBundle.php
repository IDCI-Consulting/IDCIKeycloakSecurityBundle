<?php

namespace NTI\KeycloakSecurityBundle;

use NTI\KeycloakSecurityBundle\DependencyInjection\KeycloakSecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KeycloakSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    public function getContainerExtension()
    {
        return new KeycloakSecurityExtension();
    }
}
