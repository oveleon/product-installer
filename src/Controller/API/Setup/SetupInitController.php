<?php

namespace Oveleon\ProductInstaller\Controller\API\Setup;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Contao\System;
use Oveleon\ProductInstaller\InstallerLock;
use Oveleon\ProductInstaller\ProductTaskType;
use Oveleon\ProductInstaller\Util\ArchiveUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Check and prepare a setup.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
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
                'error'   => true,
                'message' => $this->translator->trans('setup.error.productNotFound', [], 'setup')
            ]);
        }

        $requirements = [];
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
                            'error'   => true,
                            'message' => $this->translator->trans('setup.error.productIncomplete', [], 'setup')
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
                            'error'   => true,
                            'message' => $this->translator->trans('setup.error.fileNotFoundExtended', [], 'setup')
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
                            'message' => $this->translator->trans('setup.error.fileBroken', [], 'setup')
                        ]);
                    }

                    $collection['tasks'][] = $task;

                    break;

                case ProductTaskType::COMPOSER_UPDATE->value:

                    if($task['require'] ?? null)
                    {
                        foreach ($task['require'] as $bundle => $version)
                        {
                            $version = $version ?: '0.0.0';

                            $requirements[] = [
                                'bundle' => $bundle,
                                'version' => $version,
                                'valid' => InstalledVersions::satisfies(new VersionParser(), $bundle, $version)
                            ];
                        }
                    }

                    break;
            }
        }

        return new JsonResponse([
            'collection' => $collection,
            'requirements' => $requirements
        ]);
    }
}
