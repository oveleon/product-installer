<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Contao\BackendUser;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class for set and get contao manager tasks.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/contao_manager/task',
    name:       Task::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class Task
{
    public function __construct(
        private readonly ContaoManager $contaoManager,
        private readonly TranslatorInterface $translator,
        private readonly RequestStack $requestStack,
        protected TokenStorageInterface $tokenStorage,
    ){}

    #[Route('/set',
        name: 'contao_manager_set_task',
        methods: ['POST']
    )]
    public function set(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin)
        {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        $request = $this->requestStack->getCurrentRequest();
        $tasks = $request->toArray();

        if(!count($tasks))
        {
            return new JsonResponse([
                'status' => TaskStatus::NOT_AVAILABLE->value
            ]);
        }

        $task = $this->summarizeTasks($tasks, TaskAction::COMPOSER_UPDATE);

        try {
            $response = $this->contaoManager->call(
                'task',
                'PUT',
                json_encode($task)
            );

            $status = $response->getStatusCode();
        }
        catch (Exception | TransportExceptionInterface $e)
        {
            return new JsonResponse([
                'error'  => true,
                'message' => $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if($status === Response::HTTP_BAD_REQUEST)
        {
            try {
                $response = $this->contaoManager->call('task');
                $taskData = $response->toArray();
            } catch (Exception | TransportExceptionInterface | ClientExceptionInterface | DecodingExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {}

            return new JsonResponse([
                'status'  => TaskStatus::ALREADY_RUNNING->value,
                'message' => $this->translator->trans('installer.connector.errors.manager_task_active', [], 'installer'),
                'task'    => $taskData ?? []
            ], $status);
        }

        if($status !== Response::HTTP_OK)
        {
            return new JsonResponse([
                'error' => true,
                'message' => $this->translator->trans('installer.connector.errors.global_error', [], 'installer')
            ], $status);
        }

        $output = $response->toArray();
        $output['token'] = $this->contaoManager->getToken();
        $output['updateRoute'] = $this->contaoManager->getRoute('task');

        return new JsonResponse($output, $status);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    #[Route('/get',
        name: 'contao_manager_get_tasks',
        methods: ['POST']
    )]
    public function get(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin)
        {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $response = $this->contaoManager->call('task');
            $status = $response->getStatusCode();
        }
        catch (TransportExceptionInterface $e)
        {
            return new JsonResponse([
                'error' => true,
                'message' => $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if($status !== Response::HTTP_OK)
        {
            return new JsonResponse([
                'error' => true,
                'message' => $this->translator->trans('installer.connector.errors.install_failed', [], 'installer')
            ], $status);
        }

        return new JsonResponse($response->toArray(), $status);
    }

    #[Route('/stop',
        name: 'contao_manager_stop_tasks',
        methods: ['POST']
    )]
    public function stop(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin)
        {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $response = $this->contaoManager->call('task', 'PATCH');
            $status = $response->getStatusCode();
        }
        catch (TransportExceptionInterface $e)
        {
            return new JsonResponse([
                'error' => true,
                'message' => $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if($status !== Response::HTTP_OK)
        {
            return new JsonResponse([
                'error' => true,
                'message' => $this->translator->trans('installer.connector.errors.manager_task_unstoppable', [], 'installer')
            ], $status);
        }

        return new JsonResponse($response->toArray(), $status);
    }

    #[Route('/delete',
        name: 'contao_manager_delete_tasks',
        methods: ['POST']
    )]
    public function delete(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin)
        {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $response = $this->contaoManager->call('task', 'DELETE');
            $status = $response->getStatusCode();
        }
        catch (TransportExceptionInterface $e)
        {
            return new JsonResponse([
                'error' => true,
                'message' => $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if($status !== Response::HTTP_OK)
        {
            return new JsonResponse([
                'error' => true,
                'message' => $this->translator->trans('installer.connector.errors.manager_task_not_deletable', [], 'installer')
            ], $status);
        }

        return new JsonResponse(['OK'], $status);
    }

    /**
     * Summarize tasks.
     */
    public function summarizeTasks(array $tasks, TaskAction $action): array
    {
        $singleTask = [
            'name'    => $action->value,
            'config'  => [
                'uploads' => false,
                'dry_run' => false,
                'require' => [],
                'update'  => [],
                'remove'  => []
            ]
        ];

        foreach ($tasks as $task)
        {
            if(($task['uploads'] ?? false) && !$singleTask['config']['uploads'])
            {
                $singleTask['config']['uploads'] = true;
            }

            unset(
                $task['type'],
                $task['provider'],
                $task['token'],
                $task['uploads'],
                $task['composer']
            );

            $singleTask['config'] = array_merge_recursive($singleTask['config'], $task);
        }

        return $singleTask;
    }
}
