<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Group;
use App\Domain\Entity\Skill;
use App\Domain\Entity\Student;
use App\Domain\ValueObject\EntityId;
use App\Domain\ValueObject\ProficiencyLevel;
use App\Interface\DTO\StudentFilterRequest;

interface StudentServiceInterface
{
    /**
     * Finds a student by their ID.
     * @see StudentService::findById()
     */
    public function findById(EntityId $id): ?Student;

    /**
     * Finds a skill by its ID.
     * @see StudentService::findSkillById()
     */
    public function findSkillById(EntityId $id): ?Skill;

    /**
     * Finds students based on filter criteria.
     * @return array<Student>
     * @see StudentService::findByFilter()
     */
    public function findByFilter(StudentFilterRequest $filter): array;

    /**
     * Counts students matching filter criteria.
     * @see StudentService::countByFilter()
     */
    public function countByFilter(StudentFilterRequest $filter): int;

    /**
     * Creates a new student.
     * @param array<array{skill_id: int, level: ProficiencyLevel}> $initial_skills List of initial skills with their levels
     * @see StudentService::create()
     */
    public function create(
        string $first_name,
        string $last_name,
        string $email,
        array $initial_skills = [],
    ): Student;

    /**
     * Updates student information.
     * @see StudentService::update()
     */
    public function update(
        Student $student,
        string $first_name,
        string $last_name,
        string $email,
    ): void;

    /**
     * Adds a student to a group.
     * @see StudentService::joinGroup()
     */
    public function joinGroup(Student $student, Group $group): void;

    /**
     * Removes a student from a group.
     * @see StudentService::leaveGroup()
     */
    public function leaveGroup(Student $student, Group $group): void;

    /**
     * Adds a skill to a student with specified proficiency level.
     * @see StudentService::addSkill()
     */
    public function addSkill(Student $student, Skill $skill, ProficiencyLevel $level): void;

    /**
     * Removes a skill from a student.
     * @see StudentService::removeSkill()
     */
    public function removeSkill(Student $student, Skill $skill): void;

    /**
     * Updates student's skill level.
     * @see StudentService::updateSkillLevel()
     */
    public function updateSkillLevel(Student $student, Skill $skill, ProficiencyLevel $level): void;

    /**
     * Deletes a student.
     * @see StudentService::delete()
     */
    public function delete(Student $student): void;
}
