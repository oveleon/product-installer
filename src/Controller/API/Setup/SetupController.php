<?php

namespace Oveleon\ProductInstaller\Controller\API\Setup;

use Oveleon\ProductInstaller\Import\Prompt\PromptResponse;
use Oveleon\ProductInstaller\ProductTaskType;
use Oveleon\ProductInstaller\Setup\ContentPackageSetup;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Starts the setup based on a task type.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/setup/run',
    name:       SetupController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class SetupController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly ContentPackageSetup $contentPackageSetup
    ){}

    /**
     * Product setup controller.
     *
     * Set up of the products and potential prompts for user input.
     */
    public function __invoke(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $parameter = $request->toArray();

        // Get Task hash
        $taskHash = $parameter['task'];

        // Create PromptResponse
        $promptResponse = new PromptResponse($parameter['promptResponse'] ?? []);

        foreach ($parameter['tasks'] ?? [] as $task)
        {
            // Extend task options with expert-mode information
            $task['expert'] = $parameter['expert'];
            $task['productHash'] = $parameter['product'];

            // Get current task
            if($task['hash'] === $taskHash)
            {
                switch ($task['type'])
                {
                    case ProductTaskType::CONTENT_PACKAGE->value:
                        return $this->contentPackageSetup->run($task, $promptResponse);
                }
            }
        }

        return new JsonResponse([
            'error'   => true,
            'message' => $this->translator->trans('setup.error.setupNotFound', [], 'setup')
        ]);
    }
}
