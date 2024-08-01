<?php

namespace Oveleon\ProductInstaller\Controller\API\Upload;

use Contao\BackendUser;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Oveleon\ProductInstaller\ProductTaskType;
use Oveleon\ProductInstaller\Util\ArchiveUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class to upload files via License Connector Upload.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('api/upload/product/upload',
    name:       UploadController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class UploadController
{
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly ContaoFramework $framework,
        protected readonly TranslatorInterface $translator,
        protected readonly ArchiveUtil $archiveUtil,
        protected TokenStorageInterface $tokenStorage,
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin)
        {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        $this->framework->initialize();

        $request = $this->requestStack->getCurrentRequest();

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        $root = System::getContainer()->getParameter('kernel.project_dir');
        $destination = 'product-installer' . DIRECTORY_SEPARATOR . 'downloads';
        $filename = $file->getClientOriginalName();
        $installable = false;

        $file->move(
            $root . DIRECTORY_SEPARATOR . $destination,
            $filename
        );

        if($manifest = $this->archiveUtil->getFileContent($root . DIRECTORY_SEPARATOR . $destination . DIRECTORY_SEPARATOR . $filename,'content.manifest.json', true))
        {
            $manifest['hash'] = hash('sha256', json_encode($manifest));
            $manifest['type'] = 'product';
            $manifest['updated'] = time();
            $manifest['tasks'] = array_merge($manifest['tasks'] ?? [], [
                [
                    'hash'        => hash('sha256', $filename),
                    'type'        => ProductTaskType::CONTENT_PACKAGE->value,
                    'destination' => $destination . DIRECTORY_SEPARATOR . $filename
                ]
            ]);

            $installable = version_compare(ContaoCoreBundle::getVersion(), $manifest['max_contao_version']) <= 0;
        }
        else
        {
            $manifest = [
                'error'   => true,
                'message' => $this->translator->trans('installer.connector.upload.errors.package_incomplete', [], 'installer')
            ];
        }

        return new JsonResponse([
            'products'    => [$manifest],
            'installable' => $installable,
        ]);
    }
}
