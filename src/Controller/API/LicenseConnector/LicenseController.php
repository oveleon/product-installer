<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Validates the passed license against the given License Connector.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/license_connector/license',
    name:       LicenseController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class LicenseController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly ConnectorUtil $connectorUtil
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $parameter = $request->toArray();

        // Check if a license has been submitted
        if(!$license = $parameter['license'])
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => $this->translator->trans('installer.license.errors.license_empty', [], 'installer')
                ]
            ]);
        }

        // Get current connector
        if(!$connector = $this->connectorUtil->getConnectorByName($parameter['connector']))
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => $this->translator->trans('installer.connector.errors.connector_not_available', [], 'installer')
                ]
            ]);
        }

        // Check license via connector
        $response = $this->connectorUtil->post(
            $connector['connector'],
            '/license/check',
            array_merge(
                $parameter,
                [
                    'locale' => $request->getLocale(),
                    'host'   => $request->getHost()
                ]
            )
        );

        // Check whether a connection could be established
        if($response->getStatusCode() !== Response::HTTP_OK)
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => $this->translator->trans('installer.connector.errors.connection_failed_global', [], 'installer')
                ]
            ]);
        }

        $licenseInformation = $response->toArray();

        if($error = ($licenseInformation['error'] ?? false))
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => $error
                ]
            ]);
        }

        return new JsonResponse([
            'products' => $licenseInformation['products'] ?? []
        ]);
    }
}
