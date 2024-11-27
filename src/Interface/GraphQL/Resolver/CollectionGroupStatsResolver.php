<?php

declare(strict_types=1);

namespace App\Interface\GraphQL\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use App\Domain\Dto\GroupStatistics;
use App\Domain\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Psr\Log\LoggerInterface;

final class CollectionGroupStatsResolver implements QueryCollectionResolverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(?iterable $collection, array $context): TraversablePaginator
    {
        // Логируем входящие параметры
        $this->logger->debug('GraphQL context args:', [
            'args' => $context['args'] ?? []
        ]);

        $qb = $this->em->getRepository(Group::class)->createQueryBuilder('g')
            ->select('g')
            ->leftJoin('g.students', 's')
            ->leftJoin('g.teacher', 't');

        $offset = $context['args']['offset'] ?? 0;
        $limit = $context['args']['first'] ?? 30;

        // Проверяем значение has_available_slots
        $has_available_slots = $context['args']['has_available_slots'] ?? null;
        $this->logger->debug('Filter value:', [
            'has_available_slots' => $has_available_slots
        ]);

        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $doctrine_paginator = new DoctrinePaginator($qb);

        $groups = iterator_to_array($doctrine_paginator);

        // Логируем количество групп до фильтрации
        $this->logger->debug('Groups before filter:', [
            'count' => count($groups)
        ]);

        // Фильтруем группы
        if ($has_available_slots !== null) {
            $groups = array_filter($groups, function (Group $group) use ($has_available_slots) {
                $can_accept = $group->canAcceptMoreStudents();
                // Логируем каждую группу
                $this->logger->debug('Group filter check:', [
                    'group_id' => $group->getId(),
                    'can_accept' => $can_accept,
                    'students_count' => $group->getStudents()->count(),
                    'max_students' => $group->getMaxStudents()
                ]);
                return $has_available_slots ? $can_accept : !$can_accept;
            });
        }

        // Логируем количество групп после фильтрации
        $this->logger->debug('Groups after filter:', [
            'count' => count($groups)
        ]);

        $results = array_map(
            function (Group $group) {
                return new GroupStatistics(
                    $group->getId(),
                    $group->getName(),
                    $group->getStudents()->count(),
                    $this->calculateCapacityPercentage($group),
                    !$group->canAcceptMoreStudents(),
                    $group->getMaxStudents() - $group->getStudents()->count(),
                    sprintf(
                        '%s %s',
                        $group->getTeacher()->getFirstName(),
                        $group->getTeacher()->getLastName()
                    )
                );
            },
            $groups
        );

        return new TraversablePaginator(
            new \ArrayIterator($results),
            $offset,
            $limit,
            count($groups)
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
