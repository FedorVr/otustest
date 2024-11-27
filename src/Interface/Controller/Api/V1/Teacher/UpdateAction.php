<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use DomainException;
use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Name;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\TeacherResponse;
use App\Interface\DTO\UpdateTeacherRequest;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers/{id}', methods: ['PUT'])]
final class UpdateAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateTeacherRequest $request,
    ): JsonResponse {
        try {
            $teacher_id = new EntityId($id);
            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            $first_name = $request->first_name !== null ? new Name($request->first_name) : null;
            $last_name = $request->last_name !== null ? new Name($request->last_name) : null;
            $email = $request->email !== null ? new Email($request->email) : null;

            $this->teacher_service->update(
                teacher: $teacher,
                first_name: $first_name?->getValue() ?? $teacher->getFirstName(),
                last_name: $last_name?->getValue() ?? $teacher->getLastName(),
                email: $email?->getValue() ?? $teacher->getEmail(),
                max_groups: $request->max_groups ?? $teacher->getMaxGroups(),
            );

            return $this->json(TeacherResponse::fromEntity($teacher));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
