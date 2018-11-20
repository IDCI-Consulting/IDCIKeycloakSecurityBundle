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
        $bundles = $container->getParameter('kernel.bundles');

        if (!isset($bundles['KnpUOAuth2ClientBundle'])) {
            throw new \LogicException(
                'You must install knpuniversity/oauth2-client-bundle in order to use IDCIKeycloakSecurityBundle'
            );
        }

        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->prependExtensionConfig('knpu_oauth2_client', $this->generateKeycloakAuthConfiguration($container));
    }

    protected function generateKeycloakAuthConfiguration(ContainerBuilder $container)
    {
        return [
            'clients' => [
                'keycloak' => [
                    'type' => 'generic',
                    'provider_class' => 'IDCI\Bundle\KeycloakSecurityBundle\Provider\Keycloak',
                    'client_id' => $container->getParameter('keycloak_client_id'),
                    'client_secret' => $container->getParameter('keycloak_client_secret'),
                    'redirect_route' => 'idci_security_auth_connect_check_keycloak',
                    'redirect_params' => [],
                    'provider_options' => [
                        'auth_server_private_url' => $container->hasParameter('keycloak_server_private_url') ?
                            $container->getParameter('keycloak_server_private_url') : null,
                        'auth_server_public_url' => $container->hasParameter('keycloak_server_public_url') ?
                            $container->getParameter('keycloak_server_public_url') : null,
                        'realm' => $container->getParameter('keycloak_realm'),
                    ],
                ],
            ],
        ];
    }

    public function getAlias()
    {
        return 'idci_keycloak_security';
    }
}
