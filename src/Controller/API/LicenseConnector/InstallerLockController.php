<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Oveleon\ProductInstaller\InstallerLock;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Helper functions to edit the installer lock file via the API.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
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
