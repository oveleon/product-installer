<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('%contao.backend.route_prefix%/api/license_connector/advertising',
    name:       AdvertisingController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class AdvertisingController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ConnectorUtil $connectorUtil
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $parameter = $request->toArray();

        // Get current connector
        if(!$connector = $this->connectorUtil->getConnectorByName($parameter['connector']))
        {
            return new JsonResponse([
                'error'  => true,
                'message' => 'No license connector found.'
            ]);
        }

        // Get advertising
        $response = $this->connectorUtil->post(
            $connector['connector'],
            '/advertising/get'
        );

        // Check whether a connection could be established
        if($response->getStatusCode() !== Response::HTTP_OK)
        {
            return new JsonResponse([
                'error'  => true,
                'message' => 'No connection can be established at the moment, please try again later.'
            ]);
        }

        $advertising = $response->toArray();

        if(empty($advertising))
        {
            return new JsonResponse(['type' => 'skip']);
        }

        return new JsonResponse($advertising);
    }
}
