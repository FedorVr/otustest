<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\GraphQl\Mutation;
use App\Domain\ValueObject\GroupCapacity;
use App\Interface\GraphQL\Resolver\UpdateGroupNameResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity]
#[ORM\Table(name: 'groups')]
#[ORM\Index(name: 'groups__name__idx', columns: ['name'])]
#[ORM\Index(name: 'groups__teacher_id__idx', columns: ['teacher_id'])]
# API-platform
#[ApiFilter(SearchFilter::class, properties: [
    'teacher.first_name' => 'partial',
    'teacher.last_name' => 'partial',
])]
// API-platform
#[ApiResource(
    shortName: 'Group',
    graphQlOperations: [
        new Mutation(
            resolver: UpdateGroupNameResolver::class,
            args: [
                'id' => ['type' => 'ID!', 'description' => 'The ID of the teacher.'],
                'name' => ['type' => 'String!', 'description' => 'The new name of the teacher.']
            ],
            name: 'updateName'
        )
    ]
)]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['group:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['group:read'])]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'groups')]
    #[ORM\JoinColumn(name: 'teacher_id', nullable: false)]
    #[Groups(['group:read'])]
    private Teacher $teacher;

    #[ORM\ManyToMany(targetEntity: Student::class, mappedBy: 'groups')]
    #[Groups(['group:read'])]
    private Collection $students;

    #[ORM\OneToMany(targetEntity: SkillProficiency::class, mappedBy: 'group', cascade: ['persist', 'remove'])]
    private Collection $skills;

    #[ORM\Column(name: 'min_students', type: Types::INTEGER)]
    #[Groups(['group:read'])]
    private int $min_students = 1;

    #[ORM\Column(name: 'max_students', type: Types::INTEGER)]
    #[Groups(['group:read'])]
    private int $max_students = 20;

    private GroupCapacity $capacity;

    public function __construct(
        string $name,
        Teacher $teacher,
        int $min_students = 1,
        int $max_students = 20,
    ) {
        $this->name = $name;
        $this->teacher = $teacher;
        $this->capacity = new GroupCapacity($min_students, $max_students);

        // Сохраняем значения для Doctrine
        $this->min_students = $min_students;
        $this->max_students = $max_students;

        $this->students = new ArrayCollection();
        $this->skills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(Teacher $teacher): void
    {
        // Удаляем группу у текущего учителя
        if ($this->teacher !== $teacher) {
            $this->teacher->removeGroup($this);
        }

        $this->teacher = $teacher;
        $teacher->addGroup($this);
    }

    public function addTeacher(Teacher $teacher): void
    {
        $this->setTeacher($teacher);
    }

    public function removeTeacher(Teacher $teacher): void
    {
        if ($this->teacher === $teacher) {
            $this->teacher->removeGroup($this);
            $this->teacher = $teacher; // Сохраняем текущего учителя, так как поле не может быть null
        }
    }

    /**
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): void
    {
        if (!$this->capacity->canAcceptMoreStudents($this->students->count())) {
            throw new DomainException('Group has reached maximum capacity');
        }

        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->addGroup($this);
        }
    }

    public function removeStudent(Student $student): void
    {
        if ($this->students->removeElement($student)) {
            $student->removeGroup($this);
        }
    }

    /**
     * @return Collection<int, SkillProficiency>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    /**
     * @return Collection<int, SkillProficiency>
     */
    #[SerializedName('required_skills')]
    public function getRequiredSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(SkillProficiency $skill): void
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->setGroup($this);
        }
    }

    public function removeSkill(SkillProficiency $skill): void
    {
        if ($this->skills->removeElement($skill)) {
            $skill->setGroup(null);
        }
    }

    public function removeSkillBySkill(Skill $skill): void
    {
        foreach ($this->skills as $skill_proficiency) {
            if ($skill_proficiency->getSkill() === $skill) {
                $this->removeSkill($skill_proficiency);
                break;
            }
        }
    }

    #[SerializedName('min_students')]
    public function getMinStudents(): int
    {
        return $this->min_students;
    }

    #[SerializedName('max_students')]
    public function getMaxStudents(): int
    {
        return $this->max_students;
    }

    public function canAcceptMoreStudents(): bool
    {
        return $this->students->count() < $this->max_students;
    }

    #[SerializedName('remaining_capacity')]
    public function getRemainingCapacity(): int
    {
        return $this->max_students - $this->students->count();
    }

    #[SerializedName('minimum_students')]
    public function hasMinimumStudents(): bool
    {
        return $this->students->count() >= $this->min_students;
    }

    public function getCapacity(): GroupCapacity
    {
        return $this->capacity;
    }

    public function setCapacity(int $min_students, int $max_students): void
    {
        $this->capacity = new GroupCapacity($min_students, $max_students);
        $this->min_students = $min_students;
        $this->max_students = $max_students;
    }

    public function setMinStudents(int $min_students): void
    {
        if ($min_students < 1) {
            throw new DomainException('Minimum number of students cannot be less than 1');
        }
        if ($min_students > $this->max_students) {
            throw new DomainException('Minimum number of students cannot be greater than maximum');
        }
        $this->min_students = $min_students;
        $this->capacity = new GroupCapacity($min_students, $this->max_students);
    }

    public function setMaxStudents(int $max_students): void
    {
        if ($max_students < $this->min_students) {
            throw new DomainException('Maximum number of students cannot be less than minimum');
        }
        $this->max_students = $max_students;
        $this->capacity = new GroupCapacity($this->min_students, $max_students);
    }

    #[ORM\PostLoad]
    public function initializeCapacity(): void
    {
        $this->capacity = new GroupCapacity($this->min_students, $this->max_students);
    }

    #[Groups(['group:read'])]
    #[SerializedName('students_count')]
    public function getStudentsCount(): int
    {
        return $this->students->count();
    }
}
