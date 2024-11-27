<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\Service\TeacherServiceInterface;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\CreateGroupRequest;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups', methods: ['POST'])]
final class CreateAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly TeacherServiceInterface $teacher_service,
        private readonly SkillServiceInterface $skill_service,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] CreateGroupRequest $request,
    ): JsonResponse {
        try {
            $teacher_id = new EntityId($request->teacher_id);

            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            // Создаем группу без студентов
            $group = $this->group_service->create(
                $request->name,
                [],  // пустой массив студентов
                $request->min_students,
                $request->max_size,
                $teacher,
            );

            // Добавляем требуемые навыки
            foreach ($request->required_skills as $skill_data) {
                $skill_id = new EntityId($skill_data['skill_id']);
                $skill = $this->skill_service->findById($skill_id);
                $this->validateEntityExists($skill, 'Skill not found');

                $level = match ($skill_data['level']) {
                    1 => new ProficiencyLevel('beginner'),
                    2 => new ProficiencyLevel('intermediate'),
                    3, 4 => new ProficiencyLevel('advanced'),
                    5 => new ProficiencyLevel('expert'),
                    default => throw ApiException::validationError('Invalid skill level'),
                };
                $this->group_service->addSkill($group, $skill, $level);
            }

            return $this->json(GroupResponse::fromEntity($group), Response::HTTP_CREATED);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
