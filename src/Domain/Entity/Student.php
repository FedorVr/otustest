<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PersonName;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'students')]
// API-platform
#[ApiResource]
class Student extends User
{
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'students')]
    #[ORM\JoinTable(name: 'student_groups')]
    private Collection $groups;

    #[ORM\OneToMany(targetEntity: SkillProficiency::class, mappedBy: 'student', cascade: ['persist', 'remove'])]
    private Collection $skills;

    public function __construct(
        string $first_name,
        string $last_name,
        string $email,
        array $roles = ['ROLE_STUDENT'],
        string $password = '',
    ) {
        parent::__construct($first_name, $last_name, $email, $roles, $password);
        $this->groups = new ArrayCollection();
        $this->skills = new ArrayCollection();
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): void
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addStudent($this);
        }
    }

    public function removeGroup(Group $group): void
    {
        if ($this->groups->removeElement($group)) {
            $group->removeStudent($this);
        }
    }

    /**
     * @return Collection<int, SkillProficiency>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(SkillProficiency $skill): void
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->setStudent($this);
        }
    }

    public function removeSkill(SkillProficiency $skill): void
    {
        if ($this->skills->removeElement($skill)) {
            $skill->setStudent(null);
        }
    }

    public function updateName(PersonName $name): void
    {
        parent::updateName($name);
    }

    public function updateEmail(Email $email): void
    {
        parent::updateEmail($email);
    }
}
