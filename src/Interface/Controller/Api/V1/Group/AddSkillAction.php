<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\DTO\GroupResponse;
use App\Interface\DTO\UpdateSkillProficiencyRequest;
use App\Interface\Exception\ApiException;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{id}/skills', methods: ['POST'])]
final class AddSkillAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateSkillProficiencyRequest $request,
    ): JsonResponse {
        try {
            $group_id = new EntityId($id);

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            $skill = $this->group_service->findSkillById(new EntityId($request->skill_id));
            $this->validateEntityExists($skill, 'Skill not found');

            $level = new ProficiencyLevel($request->level);

            $this->group_service->addRequiredSkill($group, $skill, $level);

            return $this->json(GroupResponse::fromEntity($group));
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
