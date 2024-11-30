<?php

declare(strict_types=1);

namespace App\Interface\Controller\Api\V1\Group;

use App\Domain\Service\GroupServiceInterface;
use App\Domain\ValueObject\EntityId;
use App\Interface\Controller\Api\V1\ApiController;
use App\Interface\Exception\ApiException;
use DomainException;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/v1/groups/{id}', methods: ['DELETE'])]
final class DeleteAction extends ApiController
{
    public function __construct(
        private readonly GroupServiceInterface $group_service,
        private readonly ProducerInterface $cache_invalidation_producer,
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $group_id = new EntityId($id);

            $group = $this->group_service->findById($group_id);
            $this->validateEntityExists($group, 'Group not found');

            $this->group_service->delete($group);

            // Инвалидируем кэш группы и списка групп
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'group',
                'id' => $id,
            ]));
            $this->cache_invalidation_producer->publish(json_encode([
                'type' => 'group_list',
                'id' => 'all',
            ]));

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (DomainException $e) {
            throw ApiException::fromDomainException($e);
        }
    }
}
