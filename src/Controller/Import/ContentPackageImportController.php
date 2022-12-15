<?php

namespace Oveleon\ProductInstaller\Controller\Import;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Exception;
use Oveleon\ProductInstaller\Import\ContentPackageImport;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('%contao.backend.route_prefix%/content/import',
    name:       ContentPackageImportController::class,
    defaults:   ['_scope' => 'backend']
)]
class ContentPackageImportController
{
    public function __construct(
        private readonly ContentPackageImport $importer,
        private readonly RequestStack $requestStack
    ){}

    /**
     * @throws Exception
     */
    public function __invoke(): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        // Set the root page if one is selected
        if($rootPage = $request->get('page'))
        {
            $this->importer->setRootPage((int) $rootPage);
        }

        // Start import
        $this->importer->import($request->get('filepath'));

        throw new RedirectResponseException('/contao');
    }
}
