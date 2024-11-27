<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Group;
use App\Domain\Entity\Student;
use App\Domain\Entity\Teacher;

interface GroupRepositoryInterface
{
    public function findById(int $id): ?Group;

    /**
     * @return array<Group>
     */
    public function findAll(): array;

    public function save(Group $group): void;

    public function remove(Group $group): void;

    /**
     * @return array<Group>
     */
    public function findByTeacherId(int $teacher_id): array;

    /**
     * @return array<Group>
     */
    public function findByTeacher(Teacher $teacher): array;

    /**
     * @return array<Group>
     */
    public function findAvailableGroupsForStudent(Student $student): array;

    /**
     * @param array<string, mixed> $criteria
     */
    public function count(array $criteria = []): int;

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @return array<Group>
     */
    public function findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function isGroupFull(Group $group): bool;
}
