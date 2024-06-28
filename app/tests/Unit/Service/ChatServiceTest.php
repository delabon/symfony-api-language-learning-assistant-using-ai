<?php

namespace App\Tests\Unit\Service;

use App\Doctrine\MessageAuthorEnum;
use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Repository\MessageRepository;
use App\Service\ChatService;
use App\Tests\Trait\ConversationCreator;
use App\Tests\Trait\MessageCreator;
use App\Tests\UnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;

class ChatServiceTest extends UnitTestCase
{
    use ConversationCreator;
    use MessageCreator;

    // Method hasSystemMessage
    public function testMethodHasSystemMessageReturnsTrueWhenThereIsSystemMessage(): void
    {
        $conversation = $this->createConversationWithId(456);
        $message = $this->createMessage('Fake Message', MessageAuthorEnum::SYSTEM, $conversation);

        $entityManagerMock = $this->createStub(EntityManagerInterface::class);

        $messageRepositoryMock = $this->createMock(MessageRepository::class);
        $messageRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with([
                'conversation' => $conversation,
                'author' => MessageAuthorEnum::SYSTEM
            ])->willReturnCallback(function () use ($message) {
                $reflectionClass = new ReflectionClass($message);
                $prop = $reflectionClass->getProperty('id');
                $prop->setValue($message, 43);

                return $message;
            });

        $chatService = new ChatService($messageRepositoryMock, $entityManagerMock);

        $this->assertTrue($chatService->hasSystemMessage($conversation));
    }

    public function testMethodHasSystemMessageReturnsFalseWhenThereIsNotSystemMessage(): void
    {
        $conversation = $this->createConversationWithId(432);

        $entityManagerMock = $this->createStub(EntityManagerInterface::class);

        $messageRepositoryMock = $this->createMock(MessageRepository::class);
        $messageRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with([
                'conversation' => $conversation,
                'author' => MessageAuthorEnum::SYSTEM
            ])->willReturn(null);

        $chatService = new ChatService($messageRepositoryMock, $entityManagerMock);

        $this->assertFalse($chatService->hasSystemMessage($conversation));
    }

    // Method addSystemMessage
    public function testAddsSystemMessageSuccessfully(): void
    {
        $conversation = $this->createConversationWithId(7323);
        $body = 'You are a helpful assistant. You are a language teacher and the language you are helping me with is ' . $conversation->getLanguage()->value . '.';
        $message = $this->createMessage($body, MessageAuthorEnum::SYSTEM, $conversation);

        $messageRepositoryMock = $this->createMock(MessageRepository::class);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function () use ($message) {
                $reflectionClass = new ReflectionClass($message);
                $prop = $reflectionClass->getProperty('id');
                $prop->setValue($message, 234);
            });
        $entityManagerMock->expects($this->once())
            ->method('flush');

        $chatService = new ChatService($messageRepositoryMock, $entityManagerMock);

        $createdMessage = $chatService->addSystemMessage($conversation);

        $this->assertSame(234, $message->getId());
        $this->assertSame($conversation->getId(), $message->getConversation()->getId());
        $this->assertSame(MessageAuthorEnum::SYSTEM, $createdMessage->getAuthor());
        $this->assertSame($message->getAuthor(), $createdMessage->getAuthor());
        $this->assertSame($body, $createdMessage->getBody());
        $this->assertSame($message->getBody(), $createdMessage->getBody());
    }

    // Method addUserMessage
    public function testAddsUserMessageSuccessfully(): void
    {
        $conversation = $this->createConversationWithId(665);
        $userMessage = $this->createMessage('Could you help me with the past tenses?', MessageAuthorEnum::USER, $conversation);

        $messageRepositoryMock = $this->createMock(MessageRepository::class);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->exactly(1))
            ->method('persist')
            ->willReturnCallback(function () use ($userMessage) {
                $reflectionClass = new ReflectionClass($userMessage);
                $prop = $reflectionClass->getProperty('id');
                $prop->setValue($userMessage, 2);
            });
        $entityManagerMock->expects($this->exactly(1))
            ->method('flush');

        $chatService = new ChatService($messageRepositoryMock, $entityManagerMock);

        $createdUserMessage = $chatService->addUserMessage($conversation, $userMessage->getBody());

        $this->assertSame(2, $userMessage->getId());
        $this->assertSame($conversation->getId(), $userMessage->getConversation()->getId());
        $this->assertSame(MessageAuthorEnum::USER, $createdUserMessage->getAuthor());
        $this->assertSame($userMessage->getAuthor(), $createdUserMessage->getAuthor());
        $this->assertSame($userMessage->getBody(), $createdUserMessage->getBody());
        $this->assertSame($userMessage->getBody(), $createdUserMessage->getBody());
    }

    // Method addAssistantMessage
    public function testAddsAssistantMessageSuccessfully(): void
    {
        $conversation = $this->createConversationWithId(7323);
        $assistantMessage = $this->createMessage('Sure, should we start with the simple past?', MessageAuthorEnum::ASSISTANT, $conversation);

        $messageRepositoryMock = $this->createMock(MessageRepository::class);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function () use ($assistantMessage) {
                $reflectionClass = new ReflectionClass($assistantMessage);
                $prop = $reflectionClass->getProperty('id');
                $prop->setValue($assistantMessage, 4345);
            });
        $entityManagerMock->expects($this->once())
            ->method('flush');

        $chatService = new ChatService($messageRepositoryMock, $entityManagerMock);

        $createdMessage = $chatService->addAssistantMessage($conversation, $assistantMessage->getBody());

        $this->assertSame(4345, $assistantMessage->getId());
        $this->assertSame($conversation->getId(), $assistantMessage->getConversation()->getId());
        $this->assertSame(MessageAuthorEnum::ASSISTANT, $createdMessage->getAuthor());
        $this->assertSame($assistantMessage->getAuthor(), $createdMessage->getAuthor());
        $this->assertSame($assistantMessage->getBody(), $createdMessage->getBody());
        $this->assertSame($assistantMessage->getBody(), $createdMessage->getBody());
    }

    // Method getMethods
    public function testGetMessagesReturnsMessagesSuccessfully(): void
    {
        $conversation = new Conversation();
        $conversation->setLanguage(LanguageEnum::ENGLISH);
        $conversation->setUserEntity(new User());

        $entityManagerMock = $this->createStub(EntityManagerInterface::class);
        $messageRepositoryMock = $this->createMock(MessageRepository::class);
        $messageRepositoryMock->expects($this->once())
            ->method('findBy')
            ->with([
                'conversation' => $conversation
            ])
            ->willReturnCallback(function ($criteria) {
                $message1 = $this->createMessageWithId(234, 'Message 1', MessageAuthorEnum::SYSTEM, $criteria['conversation']);
                $message2 = $this->createMessageWithId(563, 'Message 2', MessageAuthorEnum::USER, $criteria['conversation']);

                return [
                    $message1,
                    $message2
                ];
            });

        $chatService = new ChatService($messageRepositoryMock, $entityManagerMock);

        $messages = $chatService->getMessages($conversation);

        $this->assertIsArray($messages);
        $this->assertCount(2, $messages);
        $this->assertIsArray($messages[0]);
        $this->assertIsArray($messages[1]);
        $this->assertArrayHasKey('role', $messages[0]);
        $this->assertArrayHasKey('content', $messages[0]);
        $this->assertArrayHasKey('role', $messages[1]);
        $this->assertArrayHasKey('content', $messages[1]);
        $this->assertSame(MessageAuthorEnum::SYSTEM->value, $messages[0]['role']);
        $this->assertSame(MessageAuthorEnum::USER->value, $messages[1]['role']);
        $this->assertNotEmpty('content', $messages[0]['content']);
        $this->assertIsString('content', $messages[0]['content']);
        $this->assertNotEmpty('content', $messages[1]['content']);
        $this->assertIsString('content', $messages[1]['content']);
    }

    public function testGetMessagesReturnsEmptyArrayWhenNoMessages(): void
    {
        $conversation = new Conversation();
        $conversation->setLanguage(LanguageEnum::ENGLISH);
        $conversation->setUserEntity(new User());

        $entityManagerMock = $this->createStub(EntityManagerInterface::class);
        $messageRepositoryMock = $this->createMock(MessageRepository::class);
        $messageRepositoryMock->expects($this->once())
            ->method('findBy')
            ->with([
                'conversation' => $conversation
            ])
            ->willReturn([]);

        $chatService = new ChatService($messageRepositoryMock, $entityManagerMock);

        $messages = $chatService->getMessages($conversation);

        $this->assertIsArray($messages);
        $this->assertCount(0, $messages);
    }
}
