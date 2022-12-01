<?php

namespace Oveleon\ProductInstaller\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('product_installer');
        $treeBuilder
            ->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('licenser')
                    ->prototype('scalar')
                    ->defaultNull()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
