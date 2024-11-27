<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use DomainException;
use App\Domain\Service\StudentServiceInterface;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\ListResponse;
use App\Interface\DTO\StudentFilterRequest;
use App\Interface\DTO\StudentResponse;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students', methods: ['GET'])]
final class ListAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
    ) {
    }

    public function __invoke(
        #[MapQueryString] StudentFilterRequest $filter,
    ): JsonResponse {
        try {
            $students = $this->student_service->findByFilter($filter);
            $total = $this->student_service->countByFilter($filter);

            return $this->json(
                ListResponse::create(
                    items: array_map(
                        static fn ($student) => StudentResponse::fromEntity($student),
                        $students,
                    ),
                    total: $total,
                    page: $filter->page,
                    per_page: $filter->per_page,
                )
            );
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
