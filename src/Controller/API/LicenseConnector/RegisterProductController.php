<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Contao\BackendUser;
use Oveleon\ProductInstaller\InstallerLock;
use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Registers a product in the given License Connector.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/license_connector/register',
    name:       RegisterProductController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class RegisterProductController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly ConnectorUtil $connectorUtil,
        private readonly InstallerLock $installerLock,
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
                'fields' => [
                    'license' => $this->translator->trans('installer.connector.errors.connector_not_available', [], 'installer')
                ]
            ]);
        }

        // Check license via connector
        $response = $this->connectorUtil->post(
            $connector['connector'],
            '/license/register',
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
                'error'   => true,
                'message' => $this->translator->trans('installer.connector.errors.connection_failed_global', [], 'installer')
            ]);
        }

        $responseData = $response->toArray();

        if($responseData['error'] ?? false)
        {
            return new JsonResponse([
                'error'   => true,
                'message' => $this->translator->trans('installer.connector.errors.registration_failed', [], 'installer')
            ]);
        }

        foreach ($parameter['config']['products'] ?? [] as $product)
        {
            // Add connector information
            $product['connector'] = $parameter['connector'];
            $product['setup'] = false;

            // Register product in installer-lock.json
            $this->installerLock->setProduct($product);
        }

        $this->installerLock->save();

        return new JsonResponse([
            'status' => 'ok'
        ]);
    }
}
