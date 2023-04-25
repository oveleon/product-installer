<?php

namespace Oveleon\ProductInstaller\Controller\API\Upload;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Oveleon\ProductInstaller\Import\ContentPackageImport;
use Oveleon\ProductInstaller\InstallerLock;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('api/upload/product/upload',
    name:       UploadController::class,
    defaults:   ['_scope' => 'frontend', '_token_check' => false],
    methods:    ['POST']
)]
class UploadController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ContaoFramework $framework,
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        $this->framework->initialize();

        $request = $this->requestStack->getCurrentRequest();
        $file = $request->files->get('file');

        $root = System::getContainer()->getParameter('kernel.project_dir');
        $destination = 'product-installer' . DIRECTORY_SEPARATOR . 'downloads';
        $filename = $file->getClientOriginalName();

        $file->move(
            $root . DIRECTORY_SEPARATOR . $destination,
            $filename
        );

        if($manifest = ContentPackageImport::getManifestFromArchive($root . DIRECTORY_SEPARATOR . $destination . DIRECTORY_SEPARATOR . $filename))
        {
            $manifest['connector'] = 'Upload';
            $manifest['type'] = 'product';
            $manifest['setup'] = false;
            $manifest['updated'] = time();
            $manifest['destination'] = $destination . DIRECTORY_SEPARATOR . $filename;

            $lock = new InstallerLock();
            $lock->setProduct($manifest);
            $lock->save();
        }
        else
        {
            $manifest = [
                'error'   => true,
                'message' => 'Das hochgeladene Paket ist unvollständig und kann nicht erkannt werden.'
            ];
        }

        return new JsonResponse($manifest);
    }
}
