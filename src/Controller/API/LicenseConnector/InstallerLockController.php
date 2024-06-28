<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Contao\BackendUser;
use Oveleon\ProductInstaller\InstallerLock;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

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
        private readonly InstallerLock $installerLock,
        private readonly Security $security,
    ){}

    #[Route('/remove',
        name: 'license_connector_lock_remove_product',
        methods: ['POST']
    )]
    public function remove(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin)
        {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

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
