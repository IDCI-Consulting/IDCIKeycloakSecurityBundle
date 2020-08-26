<?php

namespace NTI\KeycloakSecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('nti_keycloak_security');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('nti_keycloak_security');
        }

        $rootNode
            ->children()
                ->scalarNode('default_target_path')
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

        return $treeBuilder;
    }
}
