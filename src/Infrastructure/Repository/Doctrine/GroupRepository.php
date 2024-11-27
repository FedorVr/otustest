<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Doctrine;

use App\Domain\Entity\Group;
use App\Domain\Entity\Student;
use App\Domain\Entity\Teacher;
use App\Domain\Repository\GroupRepositoryInterface;

class GroupRepository extends AbstractBaseRepository implements GroupRepositoryInterface
{
    public function findById(int $id): ?Group
    {
        return $this->find($id);
    }

    /**
     * @return array<Group>
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    public function findByTeacherId(int $teacher_id): array
    {
        return $this->findBy(['teacher' => $teacher_id]);
    }

    public function findByTeacher(Teacher $teacher): array
    {
        return $this->findBy(['teacher' => $teacher]);
    }

    /**
     * @return array<Group>
     */
    public function findAvailableGroupsForStudent(Student $student): array
    {
        $qb = $this->createQueryBuilder('g')
            ->leftJoin('g.students', 's')
            ->where('g.max_students > SIZE(g.students)')
            ->andWhere('s IS NULL OR s != :student')
            ->andWhere(':student NOT MEMBER OF g.students')
            ->setParameter('student', $student);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function count(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('g')
            ->select('COUNT(g.id)');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("g.$field = :$field")
                ->setParameter($field, $value);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array<Group>
     */
    public function findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    public function isGroupFull(Group $group): bool
    {
        return count($group->getStudents()) >= $group->getMaxStudents();
    }
}
