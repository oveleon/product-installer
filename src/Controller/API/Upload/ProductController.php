<?php

namespace Oveleon\ProductInstaller\Controller\API\Upload;

use Contao\CoreBundle\Framework\ContaoFramework;
use Oveleon\ProductInstaller\InstallerLock;
use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('api/upload/product/all',
    name:       ProductController::class,
    defaults:   ['_scope' => 'frontend', '_token_check' => false],
    methods:    ['POST']
)]
class ProductController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ContaoFramework $framework,
        #private readonly TranslatorInterface $translator,
        #private readonly InstallerLock $installerLock,
        #private readonly ConnectorUtil $connectorUtil
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        $this->framework->initialize();

        // ToDo: Protect only BE login

        $request = $this->requestStack->getCurrentRequest();
        //$parameter = $request->toArray();

        $products = [];

        return new JsonResponse($products);
    }
}
