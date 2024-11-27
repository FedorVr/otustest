<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Auth;

use App\Domain\Repository\UserRepositoryInterface;
use App\Interface\DTO\AuthResponse;
use App\Interface\DTO\JWTTokenRequest;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
#[Route('/v1/auth/token', name: 'api_v1_auth_token', methods: ['POST'])]
final class JWTTokenController extends AbstractController
{
    public function __construct(
        private readonly UserRepositoryInterface $user_repository,
        private readonly UserPasswordHasherInterface $password_hasher,
        private readonly JWTTokenManagerInterface $jwt_manager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $dto = $this->validateCredentials($request);
            $user = $this->user_repository->findOneBy(['email' => $dto->getUser()]);
            if (!$user) {
                throw new BadRequestHttpException('Invalid credentials');
            }

            if (!$this->password_hasher->isPasswordValid($user, $dto->getPassword())) {
                throw new BadRequestHttpException('Invalid credentials');
            }

            $token = $this->jwt_manager->create($user);

            return $this->json(AuthResponse::fromUserAndToken($token));
        } catch (BadRequestHttpException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => 'Authentication failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function validateCredentials(Request $request): JWTTokenRequest
    {
        $email = $request->getUser();
        $password = $request->getPassword();
        if ($email === null || $password === null) {
            throw new BadRequestHttpException('Invalid credentials');
        }

        $dto = new JWTTokenRequest($email, $password);
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new BadRequestHttpException(json_encode(['errors' => $errors]));
        }

        return $dto;
    }
}
