<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IDCIKeycloakSecurityExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->setParameter('idci_keycloak_security.config', $config);
        $container->setParameter('idci_keycloak_security.default_target_route_name', $config['default_target_route_name']);
        $container->setParameter('idci_keycloak_security.ssl_verification', $config['ssl_verification']);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (!isset($bundles['KnpUOAuth2ClientBundle'])) {
            throw new \LogicException('You must install knpuniversity/oauth2-client-bundle in order to use IDCIKeycloakSecurityBundle');
        }

        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->prependExtensionConfig('knpu_oauth2_client', $this->generateKeycloakAuthConfiguration($config));
    }

    protected function generateKeycloakAuthConfiguration(array $config): array
    {
        return [
            'clients' => [
                'keycloak' => [
                    'type' => 'generic',
                    'provider_class' => 'IDCI\Bundle\KeycloakSecurityBundle\Provider\KeycloakProvider',
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'redirect_route' => 'idci_keycloak_security_auth_connect_check',
                    'redirect_params' => [],
                    'provider_options' => [
                        'auth_server_private_url' => isset($config['server_private_url']) ? $config['server_private_url'] : null,
                        'auth_server_public_url' => isset($config['server_public_url']) ? $config['server_public_url'] : null,
                        'auth_server_url' => isset($config['server_url']) ? $config['server_url'] : null,
                        'realm' => $config['realm'],
                        'verify' => $config['ssl_verification'],
                    ],
                ],
            ],
        ];
    }

    public function getAlias(): string
    {
        return 'idci_keycloak_security';
    }
}
