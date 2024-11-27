<?php

declare(strict_types=1);

namespace App\Domain\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    shortName: 'GroupStatistics',
    graphQlOperations: [
        new Query(
            resolver: 'App\Interface\GraphQL\Resolver\GroupStatsResolver',
            read: false,
            name: 'statisticsGroupStatistics'
        )
    ]
)]
final class GroupStatisticsOutput
{
    public function __construct(
        #[SerializedName('group_id')]
        public readonly int $group_id,
        #[SerializedName('group_name')]
        public readonly string $group_name,
        #[SerializedName('student_count')]
        public readonly int $student_count,
        #[SerializedName('min_students')]
        public readonly int $min_students,
        #[SerializedName('max_students')]
        public readonly int $max_students,
        #[SerializedName('teacher_name')]
        public readonly string $teacher_name,
    ) {
    }
}
