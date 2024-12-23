<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use DomainException;
use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\StudentResponse;
use App\Interface\DTO\UpdateSkillProficiencyRequest;
use App\Interface\Exception\ApiException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{id}/skills', methods: ['POST'])]
final class AddSkillAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
        private readonly ProducerInterface $cache_invalidation_producer,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateSkillProficiencyRequest $request,
    ): JsonResponse {
        try {
            $student_id = new EntityId($id);
            $skill_id = new EntityId($request->skill_id);

            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            $skill = $this->student_service->findSkillById($skill_id);
            $this->validateEntityExists($skill, 'Skill not found');

            $level = ProficiencyLevel::fromInt($request->level);

            $this->student_service->addSkill($student, $skill, $level);

            // Инвалидируем кэш студента
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'student',
                'id' => $id,
            ]));

            return $this->json(StudentResponse::fromEntity($student));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
