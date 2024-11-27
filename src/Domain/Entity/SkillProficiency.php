<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Domain\ValueObject\ProficiencyLevel;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: 'skill_proficiencies')]
#[ORM\Index(name: 'skill_proficiencies__level__idx', columns: ['level'])]
#[ORM\UniqueConstraint(name: 'skill_proficiencies__unique_assignment', columns: ['skill_id', 'teacher_id', 'student_id', 'group_id'])]
// API-platform
#[ApiResource]
class SkillProficiency
{
    public const MIN_LEVEL = 1;
    public const MAX_LEVEL = 5;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Skill::class)]
    #[ORM\JoinColumn(name: 'skill_id', nullable: false)]
    private Skill $skill;

    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'skills')]
    #[ORM\JoinColumn(name: 'teacher_id', nullable: true)]
    private ?Teacher $teacher = null;

    #[ORM\ManyToOne(targetEntity: Student::class, inversedBy: 'skills')]
    #[ORM\JoinColumn(name: 'student_id', nullable: true)]
    private ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'skills')]
    #[ORM\JoinColumn(name: 'group_id', nullable: true)]
    private ?Group $group = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $level;

    public function __construct(
        Skill $skill,
        ProficiencyLevel|int $level,
        ?Teacher $teacher = null,
        ?Student $student = null,
        ?Group $group = null
    ) {
        if ($teacher === null && $student === null && $group === null) {
            throw new InvalidArgumentException('Either teacher, student, or group must be set');
        }

        $this->skill = $skill;
        $this->teacher = $teacher;
        $this->student = $student;
        $this->group = $group;
        $this->setLevel($level);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSkill(): Skill
    {
        return $this->skill;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function getLevel(): ProficiencyLevel
    {
        return new ProficiencyLevel($this->level);
    }

    private function setLevel(ProficiencyLevel|int $level): void
    {
        if ($level instanceof ProficiencyLevel) {
            $this->level = $level->toInt();
        } else {
            if ($level < self::MIN_LEVEL || $level > self::MAX_LEVEL) {
                throw new InvalidArgumentException(
                    sprintf('Level must be between %d and %d', self::MIN_LEVEL, self::MAX_LEVEL)
                );
            }
            $this->level = $level;
        }
    }

    public function setSkill(Skill $skill): void
    {
        $this->skill = $skill;
    }

    public function setTeacher(?Teacher $teacher): void
    {
        $this->teacher = $teacher;
    }

    public function setStudent(?Student $student): void
    {
        $this->student = $student;
    }

    public function setGroup(?Group $group): void
    {
        // Remove from old group
        if ($this->group !== null && $this->group !== $group) {
            $this->group->removeSkill($this);
        }

        $this->group = $group;

        // Add to new group
        if ($group !== null && !$group->getSkills()->contains($this)) {
            $group->addSkill($this);
        }
    }
}
