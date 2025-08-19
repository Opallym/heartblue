<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Activity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cet email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    // --- Profil complet ---
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $about = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $badges = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $interests = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $gallery = [];

    #[ORM\Column(nullable: true)]
    private ?int $height = null;

    #[ORM\Column(nullable: true)]
    private ?int $weight = null;

    // Relation OneToMany avec Activity
    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Activity::class, orphanRemoval: true)]
    private Collection $activities;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
    }

    // --- Getters et Setters existants ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    public function eraseCredentials(): void
    {
        // ...
    }

    // --- Getters et setters du profil complet ---
    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): static { $this->name = $name; return $this; }

    public function getAge(): ?int { return $this->age; }
    public function setAge(?int $age): static { $this->age = $age; return $this; }

    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $city): static { $this->city = $city; return $this; }

    public function getCountry(): ?string { return $this->country; }
    public function setCountry(?string $country): static { $this->country = $country; return $this; }

    public function getAbout(): ?string { return $this->about; }
    public function setAbout(?string $about): static { $this->about = $about; return $this; }

    public function getAvatar(): ?string { return $this->avatar; }
    public function setAvatar(?string $avatar): static { $this->avatar = $avatar; return $this; }

    public function getBadges(): array { return $this->badges; }
    public function setBadges(array $badges): static { $this->badges = $badges; return $this; }

    public function getInterests(): array { return $this->interests; }
    public function setInterests(array $interests): static { $this->interests = $interests; return $this; }

    public function getGallery(): array { return $this->gallery; }
    public function setGallery(array $gallery): static { $this->gallery = $gallery; return $this; }

    public function getHeight(): ?int { return $this->height; }
    public function setHeight(?int $height): static { $this->height = $height; return $this; }

    public function getWeight(): ?int { return $this->weight; }
    public function setWeight(?int $weight): static { $this->weight = $weight; return $this; }

    // --- Gestion de la relation OneToMany ---
    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection { return $this->activities; }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->setCreatedBy($this);
        }
        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            if ($activity->getCreatedBy() === $this) {
                $activity->setCreatedBy(null);
            }
        }
        return $this;
    }
}
