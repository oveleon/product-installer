<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Contao\System;
use Oveleon\ProductInstaller\InstallerLock;
use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Detects and validates the products in the given License Connector.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/license_connector/products',
    name:       ProductController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class ProductController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly InstallerLock $installerLock,
        private readonly ConnectorUtil $connectorUtil
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $parameter = $request->toArray();

        $licenseConnectors = $this->connectorUtil->getConnectors();

        if(null === $licenseConnectors)
        {
            return new JsonResponse([
                'error' => true,
                'message' => $this->translator->trans('installer.connector.errors.connector_not_available', [], 'installer')
            ]);
        }

        $connectors = null;

        foreach ($licenseConnectors as $connector)
        {
            // Get registrable products
            $response = $this->connectorUtil->post(
                $connector['connector'],
                '/product/all',
                array_merge(
                    $parameter,
                    [
                        'locale' => $request->getLocale(),
                        'host'   => $request->getHost()
                    ]
                )
            );

            try {
                $remoteProducts = $response->toArray();
                $remoteProducts = $this->reduceProducts($remoteProducts);
                $clientProducts = ($this->installerLock->getInstalledProducts($connector['config']['name']) ?? []);

                $collection = [];
                $skip = false;

                if (isset($GLOBALS['PI_HOOKS']['matchProducts']) && \is_array($GLOBALS['PI_HOOKS']['matchProducts']))
                {
                    foreach ($GLOBALS['PI_HOOKS']['matchProducts'] as $callback)
                    {
                        // If the callback returns true, the system logic will be skipped
                        $skip = System::importStatic($callback[0])->{$callback[1]}($connector['config']['name'], $remoteProducts, $clientProducts, $collection);
                    }
                }

                if(!$skip)
                {
                    foreach ($clientProducts as $product)
                    {
                        $hash = $product['hash'];

                        // Product is registered and valid
                        if(
                            array_key_exists($hash, $remoteProducts) &&
                            strtolower($request->getHost()) === strtolower($remoteProducts[$hash]['license']['acceptedHost'])
                        )
                        {
                            // Copy product information
                            $p = $remoteProducts[$hash];

                            // Enrich data
                            $p['registered'] = true;
                            $p['remove'] = false;
                            $p['setup'] = $product['setup']; // ToDo: We need to check if the product needs setup by e.g. TaskTypes... (Otherwise, the possibility of setting up each product will be given)
                            $p['latestVersion'] = $p['version'];
                            $p['updated'] = $product['updated'];
                            $p['version'] = $product['version'];

                            // Add product to collection
                            $collection[$hash] = $p;
                        }
                        // Product is registered but not valid anymore
                        else
                        {
                            // Add product to collection
                            $product['registered'] = true;
                            $product['remove'] = true;
                            $product['setup'] = true;

                            $collection[$hash] = $product;
                        }
                    }
                }

                $connectors[] = [
                    'connector' => $connector['config'],
                    'products'  => array_values($collection)
                ];
            }catch (\Exception $e){
                $connectors[] = [
                    'connector' => $connector['config'],
                    'error'     => true,
                    'products'  => null,
                    'message'   => $this->translator->trans('installer.connector.errors.connection_failed', ['%connectorTitle%' => $connector['config']['title']], 'installer')
                ];
            }
        }

        return new JsonResponse($connectors);
    }

    /**
     * Reduce all types of products (packages and products) and return only product types
     */
    private function reduceProducts($products)
    {
        $collection = [];

        foreach ($products as $product)
        {
            if('package' === $product['type'])
            {
                foreach ($product['package'] as $p)
                {
                    $collection = $collection + $this->reduceProducts([$p]);
                }
            }
            else
            {
                $collection[$product['hash']] = $product;
            }
        }

        return $collection;
    }
}
