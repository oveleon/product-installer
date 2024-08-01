<?php

namespace Oveleon\ProductInstaller\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('product_installer');
        $treeBuilder
            ->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('installer_path')
                    ->cannotBeEmpty()
                    ->defaultValue('product-installer')
                ->end()
                ->arrayNode('license_connectors')
                    ->prototype('scalar')
                    ->defaultNull()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
