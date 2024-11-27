<?php

declare(strict_types=1);

namespace App\Interface\GraphQL\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Interface\GraphQL\Resolver\CollectionGroupStatsResolver;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Провайдер статистики по группам
 */
#[AutoconfigureTag('api_platform.state_provider')]
final readonly class GroupsStatsProvider implements ProviderInterface
{
    public function __construct(
        private CollectionGroupStatsResolver $resolver
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {

        // Используем резолвер для обработки фильтров и маппинга
        return $this->resolver->__invoke(null, $context);
    }
}
