<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Oveleon\ProductInstaller\InstallerLock;
use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
                'message' => 'No license connector found.'
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
                $clientProducts = ($this->installerLock->getInstalledProducts() ?? []);

                $collection = [];

                foreach ($clientProducts as $product)
                {
                    $hash = $product['hash'];

                    // Product is installed and valid
                    if(
                        array_key_exists($hash, $remoteProducts) &&
                        strtolower($request->getHost()) === strtolower($remoteProducts[$hash]['license']['acceptedHost'])
                    )
                    {
                        // Copy product information
                        $p = $remoteProducts[$hash];

                        // Enrich data
                        $p['installed'] = true;
                        $p['remove'] = false;
                        $p['latestVersion'] = $p['version'];
                        $p['updated'] = $product['updated'];
                        $p['version'] = $product['version'];

                        // Add product to collection
                        $collection[$hash] = $p;
                    }
                    // Product is installed but not valid anymore
                    else
                    {
                        // Add product to collection
                        $product['installed'] = true;
                        $product['remove'] = true;

                        $collection[$hash] = $product;
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
