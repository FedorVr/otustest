<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use DomainException;
use App\Domain\Service\StudentServiceInterface;
use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\StudentResponse;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{id}/groups/{groupId}', methods: ['POST'])]
final class JoinGroupAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
        private readonly GroupServiceInterface $group_service,
    ) {
    }

    public function __invoke(int $id, int $groupId): JsonResponse
    {
        try {
            $student_id = new EntityId($id);
            $group_id = new EntityId($groupId);

            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            // Check if student has required skills for the group
            if (!$this->group_service->hasRequiredSkills($student, $group)) {
                throw ApiException::validationError('Student does not have required skills for this group');
            }

            // Check if group has available slots
            if (!$this->group_service->hasAvailableSlots($group)) {
                throw ApiException::validationError('Group is full');
            }

            $this->student_service->joinGroup($student, $group);

            return $this->json(StudentResponse::fromEntity($student));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
