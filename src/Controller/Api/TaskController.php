<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tasks', name: 'api_tasks_')]
final class TaskController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskRepository $taskRepository,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $task = new Task();
        $task->setTitle($data['title'] ?? '');
        $task->setDescription($data['description'] ?? null);
        $task->setStatus($data['status'] ?? 'open');

        $errors = $this->validator->validate($task);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(
                ['errors' => $errorMessages],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json(
            $task,
            JsonResponse::HTTP_CREATED,
            [],
            [
                'json_encode_options' =>
                    JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_SLASHES |
                    JSON_UNESCAPED_UNICODE,
            ]
        );
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $limit = (int) $request->query->get('limit', 10);
        $page = (int) $request->query->get('page', 1);

        $limit = max(1, min($limit, 100));
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;

        $tasks = $this->taskRepository->findBy([], ['created_at' => 'DESC'], $limit, $offset);

        $total = $this->taskRepository->count([]);
        $totalPages = (int) ceil($total / $limit);

        return $this->json(
            [
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => $totalPages,
                ],
                'data' => $tasks,
            ],
            JsonResponse::HTTP_OK,
            [],
            [
                'json_encode_options' =>
                    JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_SLASHES |
                    JSON_UNESCAPED_UNICODE,
            ]
        );
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json(
                ['error' => 'Task not found'],
                JsonResponse::HTTP_NOT_FOUND,
            );
        }

        return $this->json(
            $task,
            JsonResponse::HTTP_OK,
            [],
            [
                'json_encode_options' =>
                    JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_SLASHES |
                    JSON_UNESCAPED_UNICODE,
            ]
        );
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json(
                ['error' => 'Task not found'],
                JsonResponse::HTTP_NOT_FOUND,
            );
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $method = $request->getMethod();
        if ($method === 'PUT') {
            $task->setTitle($data['title'] ?? '');
            $task->setDescription($data['description'] ?? null);
            $task->setStatus($data['status'] ?? 'open');
        } elseif ($method === 'PATCH') {
            if (array_key_exists('title', $data)) {
                $task->setTitle($data['title']);
            }
            if (array_key_exists('description', $data)) {
                $task->setDescription($data['description']);
            }
            if (array_key_exists('status', $data)) {
                $task->setStatus($data['status']);
            }
        }

        $errors = $this->validator->validate($task);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(
                ['errors' => $errorMessages],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $this->entityManager->flush();

        return $this->json(
            $task,
            JsonResponse::HTTP_OK,
            [],
            [
                'json_encode_options' =>
                    JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_SLASHES |
                    JSON_UNESCAPED_UNICODE,
            ]
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json(
                ['error' => 'Task not found'],
                JsonResponse::HTTP_NOT_FOUND,
            );
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return $this->json(
            '',
            JsonResponse::HTTP_NO_CONTENT,
        );
    }
}
