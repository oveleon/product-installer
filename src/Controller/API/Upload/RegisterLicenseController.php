<?php

namespace Oveleon\ProductInstaller\Controller\API\Upload;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/upload/license/register',
    name:       RegisterLicenseController::class,
    defaults:   ['_scope' => 'frontend', '_token_check' => false],
    methods:    ['POST']
)]
class RegisterLicenseController
{
    /**
     * Get products
     */
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([]);
    }
}
