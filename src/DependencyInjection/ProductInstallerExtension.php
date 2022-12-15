<?php

declare(strict_types=1);

namespace Oveleon\ProductInstaller\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ProductInstallerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        // Load configurations
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        // Set container parameter
        $container->setParameter('product_installer.license_connectors', $config['license_connectors']);
    }
}
