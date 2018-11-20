<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IDCIKeycloakSecurityExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->setParameter('idci_keycloak_security.default_target_path', $config['default_target_path']);
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (!isset($config['clients']) || count($config['clients']) < 1) {
            return;
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (!isset($bundles['KnpUOAuth2ClientBundle'])) {
            throw new \LogicException(
                'You must install knpuniversity/oauth2-client-bundle in order to use IDCIKeycloakSecurityBundle'
            );
        }

        $container->prependExtensionConfig('knpu_oauth2_client', ['clients' => $config['clients']]);
    }

    public function getAlias()
    {
        return 'idci_keycloak_security';
    }
}
