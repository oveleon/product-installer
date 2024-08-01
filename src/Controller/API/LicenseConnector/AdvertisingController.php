<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Contao\BackendUser;
use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Checks advertising measures using the given license connector.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/license_connector/advertising',
    name:       AdvertisingController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class AdvertisingController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly RequestStack $requestStack,
        private readonly ConnectorUtil $connectorUtil,
        protected TokenStorageInterface $tokenStorage,
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin)
        {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        $request = $this->requestStack->getCurrentRequest();
        $parameter = $request->toArray();

        // Get current connector
        if(!$connector = $this->connectorUtil->getConnectorByName($parameter['connector']))
        {
            return new JsonResponse([
                'error'  => true,
                'message' => $this->translator->trans('installer.connector.errors.connector_not_available', [], 'installer')
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
                'message' => $this->translator->trans('installer.connector.errors.connection_failed_global', [], 'installer')
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
