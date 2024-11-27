<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupFilterRequest;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups', methods: ['GET'])]
final class ListAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $filter = new GroupFilterRequest(
                search: $request->query->get('search'),
                teacher_ids: $request->query->all('teacher_ids'),
                required_skill_ids: $request->query->all('required_skill_ids'),
                has_available_spots: $request->query->getBoolean('has_available_spots'),
                page: (int) $request->query->get('page', 1),
                per_page: (int) $request->query->get('per_page', 20),
                sort_by: $request->query->all('sort_by'),
                sort_order: $request->query->get('sort_order', 'asc'),
            );

            $groups = $this->group_service->findByFilter($filter);
            $total = $this->group_service->countByFilter($filter);

            return $this->json([
                'items' => array_map(fn ($group) => GroupResponse::fromEntity($group), $groups),
                'total' => $total,
            ]);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
