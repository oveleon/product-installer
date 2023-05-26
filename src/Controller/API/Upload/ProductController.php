<?php

namespace Oveleon\ProductInstaller\Controller\API\Upload;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/upload/product/all',
    name:       ProductController::class,
    defaults:   ['_scope' => 'frontend', '_token_check' => false],
    methods:    ['POST']
)]
class ProductController
{
    public function __invoke(): JsonResponse
    {
        /**
         * All connectors are based on the same API, but products that have been uploaded manually do not have the same
         * information as products that come from a store system, for example. Therefore, the product check for manually
         * uploaded products is performed and prepared in the `matchProducts` hook.
         */
        return new JsonResponse([]);
    }
}
