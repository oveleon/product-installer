<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Oveleon\ProductInstaller\InstallerLock;
use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
                    'license' => 'No license connector found.'
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
                'error'  => true,
                'message' => 'No connection can be established at the moment, please try again later.'
            ]);
        }

        $responseData = $response->toArray();

        if($responseData['error'] ?? false)
        {
            return new JsonResponse([
                'error'  => true,
                'message' => 'Die Lizenz konnte nicht registriert werden, bitte versuchen Sie es zu einem späteren Zeitpunkt erneut.'
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
