<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use DomainException;
use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Name;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\StudentResponse;
use App\Interface\DTO\UpdateStudentRequest;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{id}', methods: ['PUT'])]
final class UpdateAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateStudentRequest $request,
    ): JsonResponse {
        try {
            $student_id = new EntityId($id);
            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            $first_name = $request->first_name !== null ? new Name($request->first_name) : null;
            $last_name = $request->last_name !== null ? new Name($request->last_name) : null;
            $email = $request->email !== null ? new Email($request->email) : null;

            $this->student_service->update(
                student: $student,
                first_name: $first_name?->getValue() ?? $student->getFirstName(),
                last_name: $last_name?->getValue() ?? $student->getLastName(),
                email: $email?->getValue() ?? $student->getEmail(),
            );

            return $this->json(StudentResponse::fromEntity($student));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
