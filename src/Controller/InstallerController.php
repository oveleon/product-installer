<?php

namespace Oveleon\ProductInstaller\Controller;

use Contao\Controller;
use Oveleon\ProductInstaller\Licenser\AbstractLicenser;
use Oveleon\ProductInstaller\Licenser\Step\AbstractStep;
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

        $licenserClasses = Controller::getContainer()->getParameter('product_installer.licenser');
        $collection = [];

        if(!empty($licenserClasses))
        {
            foreach ($licenserClasses as $licenserClass)
            {
                /** @var AbstractLicenser $licenser */
                $licenser = new $licenserClass();
                $steps = [];

                // Prepare Steps
                /** @var AbstractStep $step */
                foreach ($licenser->getSteps() as $step)
                {
                    $steps[] = [
                        'name'   => $step->name,
                        'routes' => $step->getRoutes(),
                    ];
                }

                $collection[] = [
                    'config' => $licenser->getConfig(),
                    'steps'  => $steps
                ];
            }

            return new JsonResponse([
                'licensers' => $collection
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'message' => 'No licenser found.'
        ]);
    }
}
