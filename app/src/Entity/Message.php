<?php

namespace App\Entity;

use App\Doctrine\MessageAuthorEnum;
use App\Repository\MessageRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue('IDENTITY')]
    #[ORM\Column]
    #[Groups(['messages.list'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Invalid message body.')]
    #[Groups(['messages.list'])]
    private string $body = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotNull]
    #[Groups(['messages.list'])]
    private ?MessageAuthorEnum $author = null;

    #[ORM\ManyToOne(cascade: ['remove'], inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Conversation $conversation = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups(['messages.list'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups(['messages.list'])]
    private ?DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getAuthor(): MessageAuthorEnum
    {
        return $this->author;
    }

    public function setAuthor(MessageAuthorEnum $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;

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
}
