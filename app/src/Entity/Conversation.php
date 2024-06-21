<?php

namespace App\Entity;

use App\Enum\LanguageEnum;
use App\Repository\ConversationRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue('IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'language_enum', length: 255)]
    private ?LanguageEnum $language = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'conversations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserInterface $userEntity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLanguage(): ?LanguageEnum
    {
        return $this->language;
    }

    public function setLanguage(LanguageEnum $language): static
    {
        $this->language = $language;

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

    public function getUserEntity(): ?UserInterface
    {
        return $this->userEntity;
    }

    public function setUserEntity(?UserInterface $userEntity): static
    {
        $this->userEntity = $userEntity;

        return $this;
    }
}
