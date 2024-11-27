<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use App\Domain\Service\TeacherServiceInterface;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\ListResponse;
use App\Interface\DTO\TeacherFilterRequest;
use App\Interface\DTO\TeacherResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers', methods: ['GET'])]
final class ListAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
    ) {
    }

    public function __invoke(
        #[MapQueryString] TeacherFilterRequest $filter,
    ): JsonResponse {
        try {
            $teachers = $this->teacher_service->findByFilter($filter);
            $total = $this->teacher_service->countByFilter($filter);

            return $this->json(
                ListResponse::create(
                    items: array_map(
                        static fn ($teacher) => TeacherResponse::fromEntity($teacher),
                        $teachers,
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
