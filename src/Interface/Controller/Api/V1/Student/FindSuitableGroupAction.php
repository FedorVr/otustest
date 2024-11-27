<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use App\Domain\Service\StudentServiceInterface;
use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupResponse;
use App\Interface\DTO\ListResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{id}/suitable-groups', methods: ['GET'])]
final class FindSuitableGroupAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
        private readonly GroupServiceInterface $group_service,
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $student_id = new EntityId($id);

            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            $suitable_groups = $this->group_service->findSuitableGroups($student);

            return $this->json(
                ListResponse::create(
                    items: array_map(
                        static fn ($group) => GroupResponse::fromEntity($group),
                        $suitable_groups,
                    ),
                    total: count($suitable_groups),
                    per_page: count($suitable_groups),
                )
            );
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
