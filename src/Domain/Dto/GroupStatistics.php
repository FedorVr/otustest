<?php

declare(strict_types=1);

namespace App\Domain\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use App\Interface\GraphQL\Provider\GroupsStatsProvider;
use App\Interface\GraphQL\Resolver\CollectionGroupStatsResolver;
use App\Interface\GraphQL\Resolver\GroupStatsResolver;

#[ApiResource(
    shortName: 'Stats',
    graphQlOperations: [
        new Query(
            resolver: GroupStatsResolver::class,
            name: 'group',
        ),
        new QueryCollection(
            resolver: CollectionGroupStatsResolver::class,
            args: [
                # Фильтры
                'has_available_slots' => ['type' => 'Boolean', 'description' => 'Filter groups with available slots'],
                # Пагинация
                'first' => ['type' => 'Int', 'description' => 'Number of items to return'],
                'offset' => ['type' => 'Int', 'description' => 'Offset from which to start returning items'],
            ],
            paginationEnabled: true,
            name: 'collectionGroup',
            provider: GroupsStatsProvider::class
        ),
    ]
)]
final class GroupStatistics
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly ?int $students_count,
        private readonly ?float $capacity_percentage,
        private readonly ?bool $is_at_capacity,
        private readonly ?int $available_slots,
        private readonly ?string $teacher_name,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStudentsCount(): ?int
    {
        return $this->students_count;
    }

    public function getCapacityPercentage(): ?float
    {
        return $this->capacity_percentage;
    }

    public function isAtCapacity(): ?bool
    {
        return $this->is_at_capacity;
    }

    public function getAvailableSlots(): ?int
    {
        return $this->available_slots;
    }

    public function getTeacherName(): ?string
    {
        return $this->teacher_name;
    }
}
