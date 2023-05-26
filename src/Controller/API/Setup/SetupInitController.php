<?php

namespace Oveleon\ProductInstaller\Controller\API\Setup;

use Contao\System;
use Oveleon\ProductInstaller\InstallerLock;
use Oveleon\ProductInstaller\ProductTaskType;
use Oveleon\ProductInstaller\Util\ArchiveUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('%contao.backend.route_prefix%/api/setup/init',
    name:       SetupInitController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class SetupInitController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly InstallerLock $installerLock,
        private readonly ArchiveUtil $archiveUtil
    ){}

    /**
     * Init product setup.
     *
     * Collecting and returning tasks that require a setup.
     */
    public function __invoke(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $parameter = $request->toArray();

        if(!$product = $this->installerLock->getProduct($parameter['hash']))
        {
            return new JsonResponse([
                'error' => true,
                'message' => 'Produkt kann nicht gefunden werden oder ist nicht mehr installiert.'
            ]);
        }

        $collection = [
            'product' => $product,
            'tasks'   => null
        ];

        $filesystem = new Filesystem();
        $root = System::getContainer()->getParameter('kernel.project_dir');

        foreach ($product['tasks'] ?? [] as $task)
        {
            switch ($task['type'])
            {
                case ProductTaskType::CONTENT_PACKAGE->value:

                    // Check if the task is complete
                    if(!$filepath = $task['destination'])
                    {
                        // Overwrite the setup flag
                        $product['setup'] = true;

                        $this->installerLock->setProduct($product);
                        $this->installerLock->save();

                        return new JsonResponse([
                            'error' => true,
                            'message' => 'Das Produkt kann nicht eingerichtet werden, da es nicht vollständig installiert wurde. Bitte registrieren Sie das Produkt erneut, bevor Sie die Einrichtung starten.'
                        ]);
                    }

                    // Check if the import file still exists.
                    if(!$filesystem->exists($root . DIRECTORY_SEPARATOR . $filepath))
                    {
                        // Overwrite the setup flag
                        $product['setup'] = true;

                        $this->installerLock->setProduct($product);
                        $this->installerLock->save();

                        return new JsonResponse([
                            'error' => true,
                            'message' => 'Das Produkt kann nicht eingerichtet werden, da die Installationsdatei nicht gefunden werden kann. Bitte registrieren Sie das Produkt erneut, bevor Sie die Einrichtung starten.'
                        ]);
                    }

                    // Check if there is a content.manifest.json in the import file
                    if(!$this->archiveUtil->getFileContent($root . DIRECTORY_SEPARATOR . $filepath, 'content.manifest.json', true))
                    {
                        // Overwrite the setup flag
                        $product['setup'] = true;

                        $this->installerLock->setProduct($product);
                        $this->installerLock->save();

                        return new JsonResponse([
                            'error' => true,
                            'message' => 'Das Produkt kann nicht eingerichtet werden, da die Installationsdatei fehlerhaft ist. Bitte registrieren Sie das Produkt erneut, bevor Sie die Einrichtung starten.'
                        ]);
                    }

                    $collection['tasks'][] = $task;

                    break;
            }
        }

        return new JsonResponse($collection);
    }
}
