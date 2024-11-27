<?php

declare(strict_types=1);

namespace App\Interface\GraphQL\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use App\Domain\Entity\Group;
use App\Domain\Dto\GroupStatistics;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Статистика по группам
 */
#[AutoconfigureTag('api_platform.graphql.resolver')]
final readonly class GroupStatsResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): GroupStatistics
    {
        /** @var Group $item */
        if (!$item) {
            throw new \RuntimeException('Group not found');
        }

        return new GroupStatistics(
            $item->getId(),
            $item->getName(),
            $item->getStudents()->count(),
            $this->calculateCapacityPercentage($item),
            $item->getStudents()->count() >= $item->getMaxStudents(),
            $item->getMaxStudents() - $item->getStudents()->count(),
            sprintf(
                '%s %s',
                $item->getTeacher()->getFirstName(),
                $item->getTeacher()->getLastName()
            )
        );
    }

    private function calculateCapacityPercentage(Group $group): float
    {
        $max_students = $group->getMaxStudents();
        if ($max_students === 0) {
            return 0.0;
        }

        return round(($group->getStudents()->count() / $max_students) * 100, 2);
    }
}
