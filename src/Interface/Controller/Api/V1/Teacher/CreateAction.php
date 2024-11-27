<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Teacher;

use DomainException;
use App\Domain\Service\TeacherServiceInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PersonName;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\CreateTeacherRequest;
use App\Interface\DTO\TeacherResponse;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/teachers', methods: ['POST'])]
final class CreateAction extends ApiController
{
    public function __construct(
        private readonly TeacherServiceInterface $teacher_service,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] CreateTeacherRequest $request,
    ): JsonResponse {
        try {
            $first_name = new PersonName($request->first_name, $request->last_name);
            $email = new Email($request->email);

            $teacher = $this->teacher_service->create(
                first_name: $first_name->getFirstName(),
                last_name: $first_name->getLastName(),
                email: $email->getValue(),
                max_groups: $request->max_groups,
            );

            return $this->json(TeacherResponse::fromEntity($teacher), Response::HTTP_CREATED);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
