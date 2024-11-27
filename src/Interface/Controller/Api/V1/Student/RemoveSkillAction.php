<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use DomainException;
use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\StudentResponse;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{id}/skills/{skillId}', methods: ['DELETE'])]
final class RemoveSkillAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
    ) {
    }

    public function __invoke(int $id, int $skillId): JsonResponse
    {
        try {
            $student_id = new EntityId($id);
            $skill_id = new EntityId($skillId);

            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            $skill = $this->student_service->findSkillById($skill_id);
            $this->validateEntityExists($skill, 'Skill not found');

            $this->student_service->removeSkill($student, $skill);

            return $this->json(StudentResponse::fromEntity($student));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
