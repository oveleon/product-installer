<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Contao\Controller;
use Oveleon\ProductInstaller\LicenseConnector\AbstractLicenseConnector;
use Oveleon\ProductInstaller\LicenseConnector\Step\AbstractStep;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('%contao.backend.route_prefix%/api/license_connector',
    name:       LicenseConnectorController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class LicenseConnectorController
{
    #[Route('/config',
        name: 'license_connectors_config',
        methods: ['POST']
    )]
    public function getLicenseConnectors(): JsonResponse
    {
        $licenseConnectors = Controller::getContainer()->getParameter('product_installer.license_connectors');

        if(!empty($licenseConnectors))
        {
            $collection = [];

            foreach ($licenseConnectors as $licenseConnector)
            {
                /** @var AbstractLicenseConnector $licenseConnector */
                $licenseConnector = new $licenseConnector();
                $steps = [];

                /** @var AbstractStep $step */
                foreach ($licenseConnector->getSteps() as $step)
                {
                    $stepConfig = [
                        'name'       => $step->name,
                        'routes'     => $step->getRoutes(),
                        'attributes' => $step->getAttributes()
                    ];

                    $steps[] = $stepConfig;
                }

                $collection[] = [
                    'config' => $licenseConnector->getConfig(),
                    'steps'  => $steps
                ];
            }

            return new JsonResponse([
                'license_connectors' => $collection
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'message' => 'No license connector found.'
        ]);
    }
}
