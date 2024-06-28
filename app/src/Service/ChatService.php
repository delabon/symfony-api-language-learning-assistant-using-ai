<?php

namespace App\Service;

use App\Doctrine\MessageAuthorEnum;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\MessageRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ChatService
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function hasSystemMessage(Conversation $conversation): bool
    {
        $systemMessage = $this->messageRepository->findOneBy([
            'conversation' => $conversation,
            'author' => MessageAuthorEnum::SYSTEM
        ]);

        return $systemMessage instanceof Message && $systemMessage->getId();
    }

    public function addSystemMessage(Conversation $conversation): Message
    {
        $message = $this->createMessage($conversation, 'You are a helpful assistant. You are a language teacher and the language you are helping me with is ' . $conversation->getLanguage()->value . '.', MessageAuthorEnum::SYSTEM);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    /**
     * @param Conversation $conversation
     * @param string $body
     * @return Message
     */
    public function addUserMessage(Conversation $conversation, string $body): Message
    {
        $message = $this->createMessage($conversation, $body, MessageAuthorEnum::USER);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    public function addAssistantMessage(Conversation $conversation, string $body): Message
    {
        $message = $this->createMessage($conversation, $body, MessageAuthorEnum::ASSISTANT);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    private function createMessage(Conversation $conversation, string $body, MessageAuthorEnum $messageAuthorEnum): Message
    {
        $message = new Message();
        $message->setConversation($conversation);
        $message->setBody($body);
        $message->setAuthor($messageAuthorEnum);
        $message->setCreatedAt(new DateTimeImmutable());
        $message->setUpdatedAt(new DateTimeImmutable());

        return $message;
    }

    /**
     * @param Conversation $conversation
     * @return array<int, array<string, string>>
     */
    public function getMessages(Conversation $conversation): array
    {
        $messages = [];

        foreach ($this->messageRepository->findBy(['conversation' => $conversation]) as $message) {
            $messages[] = [
                'role' => $message->getAuthor()->value,
                'content' => $message->getBody(),
            ];
        }

        return $messages;
    }
}
