<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PersonName;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['teacher' => Teacher::class, 'student' => Student::class])]
#[ORM\Index(name: 'users__name__idx', columns: ['first_name', 'last_name'])]
#[ORM\Index(name: 'users__type__idx', columns: ['type'])]
#[ORM\HasLifecycleCallbacks]
// API-platform
#[ApiResource]
#[ApiFilter(SearchFilter::class, properties: ['email' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC'])]
abstract class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $first_name;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $last_name;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $email;

    private ?PersonName $name = null;
    private ?Email $email_vo = null;

    #[ORM\Column]
    protected array $roles = [];

    #[ORM\Column]
    protected string $password;

    public function __construct(
        string $first_name,
        string $last_name,
        string $email,
        array $roles = [],
        string $password = '',
    ) {
        // Сохраняем значения для Doctrine
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->roles = $roles;
        $this->password = $password;
    }

    #[ORM\PostLoad]
    public function initializeValueObjects(): void
    {
        $this->name = new PersonName($this->first_name, $this->last_name);
        $this->email_vo = new Email($this->email);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function getFullName(): string
    {
        if (!isset($this->name)) {
            $this->name = new PersonName($this->first_name, $this->last_name);
        }
        return $this->name->getFullName();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): PersonName
    {
        if (!isset($this->name)) {
            $this->name = new PersonName($this->first_name, $this->last_name);
        }
        return $this->name;
    }

    public function getEmailVO(): Email
    {
        if (!isset($this->email_vo)) {
            $this->email_vo = new Email($this->email);
        }
        return $this->email_vo;
    }

    public function updateName(PersonName $name): void
    {
        $this->name = $name;
        $this->first_name = $name->getFirstName();
        $this->last_name = $name->getLastName();
    }

    public function updateEmail(Email $email): void
    {
        $this->email_vo = $email;
        $this->email = $email->getValue();
    }

    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
        $this->name = new PersonName($first_name, $this->last_name);
    }

    public function setLastName(string $last_name): void
    {
        $this->last_name = $last_name;
        $this->name = new PersonName($this->first_name, $last_name);
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->email_vo = new Email($email);
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function eraseCredentials(): void
    {
    }
}
