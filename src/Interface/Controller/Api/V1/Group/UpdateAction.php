<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use DomainException;
use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\UpdateGroupRequest;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{id}', methods: ['PUT'])]
final class UpdateAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly TeacherServiceInterface $teacher_service,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateGroupRequest $request,
    ): JsonResponse {
        try {
            $group_id = new EntityId($id);

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            // Обновляем учителя, если указан
            if ($request->teacher_id !== null) {
                $teacher_id = new EntityId($request->teacher_id);
                $teacher = $this->teacher_service->findById($teacher_id);
                $this->validateEntityExists($teacher, 'Teacher not found');

                $this->teacher_service->assignToGroup($teacher, $group);
                $this->group_service->assignTeacher($group, $teacher);
            }

            // Обновляем остальные поля группы
            $this->group_service->update(
                $group,
                $request->name,
                $request->max_students,
            );

            return $this->json(GroupResponse::fromEntity($group));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
