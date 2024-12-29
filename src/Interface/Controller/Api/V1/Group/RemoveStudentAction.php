<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{group_id}/students/{student_id}', methods: ['DELETE'])]
final class RemoveStudentAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly StudentServiceInterface $student_service,
    ) {
    }

    public function __invoke(int $group_id, int $student_id): JsonResponse
    {
        try {
            $group_id = new EntityId($group_id);
            $student_id = new EntityId($student_id);

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            // Используем оба сервиса для поддержания консистентности
            $this->student_service->leaveGroup($student, $group);
            $this->group_service->removeStudent($group, $student);

            return $this->json(GroupResponse::fromEntity($group), Response::HTTP_OK);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
