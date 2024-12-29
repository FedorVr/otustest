<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students/{student_id}', methods: ['DELETE'])]
final class DeleteAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
    ) {
    }

    public function __invoke(int $student_id): JsonResponse
    {
        try {
            $student_id = new EntityId($student_id);

            $student = $this->student_service->findById($student_id);
            $this->validateEntityExists($student, 'Student not found');

            $this->student_service->delete($student);

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
