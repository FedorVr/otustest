<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use DomainException;
use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupResponse;
use App\Interface\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{id}/skills/{skill_id}', methods: ['DELETE'])]
final class RemoveSkillAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
    ) {
    }

    public function __invoke(int $id, int $skill_id): JsonResponse
    {
        try {
            $group_id = new EntityId($id);
            $skill_id = new EntityId($skill_id);

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            $skill = $this->group_service->findSkillById($skill_id);
            $this->validateEntityExists($skill, 'Skill not found');

            $this->group_service->removeRequiredSkill($group, $skill);

            return $this->json(GroupResponse::fromEntity($group));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
