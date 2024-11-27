<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Student;

use DomainException;
use App\Domain\Service\StudentServiceInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Name;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\CreateStudentRequest;
use App\Interface\DTO\StudentResponse;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/students', methods: ['POST'])]
final class CreateAction extends ApiController
{
    public function __construct(
        private readonly StudentServiceInterface $student_service,
    ) {
    }

    public function __invoke(CreateStudentRequest $request): JsonResponse
    {
        try {
            $first_name = new Name($request->first_name);
            $last_name = new Name($request->last_name);
            $email = new Email($request->email);

            $student = $this->student_service->create(
                $first_name->getValue(),
                $last_name->getValue(),
                $email->getValue(),
                $request->initial_skills ?? [],
            );

            return $this->json(
                StudentResponse::fromEntity($student),
                Response::HTTP_CREATED
            );
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
