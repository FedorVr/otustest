<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use App\Domain\ValueObject\ProficiencyLevel;
use DomainException;
use App\Domain\Service\SkillServiceInterface;
use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\AddTeacherSkillRequest;
use App\Interface\DTO\TeacherResponse;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers/{id}/skills', methods: ['POST'])]
final class AddSkillAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
        private readonly SkillServiceInterface $skill_service,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload] AddTeacherSkillRequest $request,
    ): JsonResponse {
        try {
            $teacher_id = new EntityId($id);
            $skill_id = new EntityId($request->skill_id);

            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            $skill = $this->skill_service->findById($skill_id);
            $this->validateEntityExists($skill, 'Skill not found');

            $level = new ProficiencyLevel(match ($request->level) {
                1 => 'beginner',
                2 => 'intermediate',
                3, 4 => 'advanced',
                5 => 'expert',
                default => throw ApiException::validationError('Invalid skill level'),
            });

            $this->teacher_service->addSkill($teacher, $skill, $level);

            return $this->json(TeacherResponse::fromEntity($teacher));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
