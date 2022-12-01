<?php

namespace Oveleon\ProductInstaller\Controller;

use Contao\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('%contao.backend.route_prefix%/installer', defaults: ['_scope' => 'backend', '_token_check' => false], methods: ['POST'])]
class InstallerController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ){}

    #[Route('/getlicenser', name: 'get_licenser', methods: ['POST'])]
    public function getLicenser(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest()->toArray();
        $licensers = Controller::getContainer()->getParameter('product_installer.licenser');

        if(!empty($licensers))
        {
            foreach ($licensers as $licenser)
            {
                // ToDo: Return licenser and create a new step component to show them and start the steps included
            }
        }

        return new JsonResponse([
            'error' => true,
            'message' => 'No licenser found.'
        ]);
    }
}
