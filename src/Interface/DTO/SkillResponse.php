<?php

declare(strict_types=1);

namespace App\Interface\DTO;

use App\Domain\Entity\Skill;
use App\Domain\Entity\SkillProficiency;
use JsonSerializable;

readonly class SkillResponse implements JsonSerializable
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $description = null,
        public ?int $level = null,
    ) {
    }

    public static function fromSkill(Skill $skill): self
    {
        return new self(
            id: $skill->getId(),
            name: $skill->getName(),
            description: $skill->getDescription(),
        );
    }

    public static function fromSkillProficiency(SkillProficiency $skill_proficiency): self
    {
        $skill = $skill_proficiency->getSkill();
        return new self(
            id: $skill->getId(),
            name: $skill->getName(),
            description: $skill->getDescription(),
            level: $skill_proficiency->getLevel()->getValue(),
        );
    }

    public static function fromEntity(Skill $skill): self
    {
        return self::fromSkill($skill);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'level' => $this->level,
        ];
    }
}
