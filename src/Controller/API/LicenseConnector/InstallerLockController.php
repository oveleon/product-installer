<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Oveleon\ProductInstaller\InstallerLock;
use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('%contao.backend.route_prefix%/api/license_connector/lock',
    name:       InstallerLockController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class InstallerLockController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly InstallerLock $installerLock
    ){}

    #[Route('/remove',
        name: 'license_connector_lock_remove_product',
        methods: ['POST']
    )]
    public function remove(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $request = $request->toArray();
        $hash = $request['hash'];

        if($has = $this->installerLock->hasProduct($hash))
        {
            $this->installerLock->removeProduct($hash);
            $this->installerLock->save();
        }

        return new JsonResponse(['removed' => $has]);
    }
}