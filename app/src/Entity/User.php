<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints AS Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['username'], message: 'This username is already used.', entityClass: User::class)]
#[UniqueEntity(fields: ['email'], message: 'This email is already used.', entityClass: User::class)]
#[UniqueEntity(fields: ['apiKey'], message: 'This apiKey is already used.', entityClass: User::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_API_KEY', fields: ['apiKey'])]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The name should not be blank.')]
    #[Assert\Regex(
        pattern: '/^[a-z][a-z ]+$/i',
        message: 'The name should only contain letters and spaces.'
    )]
    #[Assert\Length(min: 3, max: 50, minMessage: 'The name should be between 3 and 50 characters long.', maxMessage: 'The name should be between 3 and 50 characters long.')]
    private string $name = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The username should not be blank.',)]
    #[Assert\Regex(
        pattern: '/^[a-z0-9][a-z0-9_]+$/',
        message: 'The username should only contain lowercase letters, numbers, and underscores.'
    )]
    #[Assert\Length(min: 3, max: 50, minMessage: 'The username should be between 3 and 50 characters long.', maxMessage: 'The username should be between 3 and 50 characters long.')]
    private string $username = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The email should not be blank.')]
    #[Assert\Email]
    private string $email = '';

    #[ORM\Column(length: 255)]
    private string $apiKey = '';

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\OneToMany(targetEntity: Conversation::class, mappedBy: 'userEntity', orphanRemoval: true)]
    private Collection $conversations;

    public function __construct()
    {
        $this->conversations = new ArrayCollection();
    }

    /**
     * @param array<string, mixed> $data
     * @return User
     */
    public static function createFromArray(array $data): User
    {
        $object = new User();
        $props = get_class_vars(User::class);

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (array_key_exists($key, $props) && method_exists($object, $method)) {
                $object->$method($value);
            }
        }

        return $object;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->setUserEntity($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getUserEntity() === $this) {
                $conversation->setUserEntity(null);
            }
        }

        return $this;
    }
}
