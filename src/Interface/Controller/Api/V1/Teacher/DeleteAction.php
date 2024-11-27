<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use DomainException;
use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers/{id}', methods: ['DELETE'])]
final class DeleteAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $teacher_id = new EntityId($id);
            $teacher = $this->teacher_service->findById($teacher_id);
            $this->validateEntityExists($teacher, 'Teacher not found');

            $this->teacher_service->delete($teacher);

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
