<?php

namespace IDCI\Bundle\KeycloakSecurityBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class IDCIKeycloakSecurityBundle extends AbstractBundle
{
    protected string $extensionAlias = 'idci_keycloak_security';
    
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('default_target_route_name')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('ssl_verification')
                    ->defaultTrue()
                    ->treatNullLike(true)
                ->end()
                ->scalarNode('server_url')->end()
                ->scalarNode('server_public_url')->end()
                ->scalarNode('server_private_url')->end()
                ->scalarNode('realm')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('client_id')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('client_secret')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $builder->setParameter('idci_keycloak_security.config', $config);
        $builder->setParameter('idci_keycloak_security.default_target_route_name', $config['default_target_route_name']);
        $builder->setParameter('idci_keycloak_security.ssl_verification', $config['ssl_verification']);
    }

    protected function generateKeycloakAuthConfiguration(array $config)
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

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
