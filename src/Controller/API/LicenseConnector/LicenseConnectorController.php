<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Returns the currently available License Connectors.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/license_connector',
    name:       LicenseConnectorController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class LicenseConnectorController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ConnectorUtil $connectorUtil
    ){}

    #[Route('/config',
        name: 'license_connectors_config',
        methods: ['POST']
    )]
    public function getLicenseConnectors(): JsonResponse
    {
        $licenseConnectors = $this->connectorUtil->getConnectors();

        if(null !== $licenseConnectors)
        {
            return new JsonResponse([
                'license_connectors' => $licenseConnectors
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'message' => $this->translator->trans('installer.connector.errors.connector_not_available', [], 'installer')
        ]);
    }
}
