<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('%contao.backend.route_prefix%/api/license_connector/products',
    name:       RegisterProductController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class ProductController
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

        // ToDo: Read e.g. installer-lock.json
        // ToDo: Get all installed products and check them with server-side information. Return config to show products in the dashboard.

        return new JsonResponse([
            'status' => 'ok'
        ]);
    }
}
