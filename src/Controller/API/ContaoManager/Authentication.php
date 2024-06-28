<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Contao\BackendUser;
use Oveleon\ProductInstaller\ContaoManagerFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * Class to handle authentication with the Contao Manager.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/contao_manager/auth',
    name:       Authentication::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class Authentication
{
    public function __construct(
        private readonly ContaoManagerFile $managerFile,
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    ){}

    public function __invoke(): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin)
        {
            return;
        }

        $request = (object) $this->requestStack->getCurrentRequest()->toArray();

        if(!$request->token)
        {
            return;
        }

        $this->managerFile->setToken($request->token);
        $this->managerFile->save();
    }
}
