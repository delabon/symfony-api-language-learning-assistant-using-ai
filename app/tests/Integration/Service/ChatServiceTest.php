<?php

namespace App\Tests\Integration\Service;

use App\Doctrine\MessageAuthorEnum;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Factory\ConversationFactory;
use App\Factory\MessageFactory;
use App\Factory\UserFactory;
use App\Repository\MessageRepository;
use App\Service\ChatService;
use App\Tests\IntegrationTestCase;
use DateTimeImmutable;

class ChatServiceTest extends IntegrationTestCase
{
    public function testMethodHasSystemMessageReturnsTrueWhenThereIsSystemMessage(): void
    {
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);

        /** @var User $user */
        $user = $userFactory->create();

        /** @var ConversationFactory $conversationFactory */
        $conversationFactory = $this->getContainer()->get(ConversationFactory::class);

        /** @var Conversation $conversation */
        $conversation = $conversationFactory->create([
            'userEntity' => $user
        ]);

        /** @var MessageFactory $messageFactory */
        $messageFactory = $this->getContainer()->get(MessageFactory::class);

        $message = $messageFactory->create([
            'conversation' => $conversation,
            'author' => MessageAuthorEnum::SYSTEM
        ]);

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);

        $chatService = new ChatService($messageRepository, $this->entityManager);

        $this->assertTrue($chatService->hasSystemMessage($conversation));
    }

    public function testMethodHasSystemMessageReturnsFalseWhenThereIsNoSystemMessage(): void
    {
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);

        /** @var User $user */
        $user = $userFactory->create();

        /** @var ConversationFactory $conversationFactory */
        $conversationFactory = $this->getContainer()->get(ConversationFactory::class);

        /** @var Conversation $conversation */
        $conversation = $conversationFactory->create([
            'userEntity' => $user
        ]);

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);

        $chatService = new ChatService($messageRepository, $this->entityManager);

        $this->assertFalse($chatService->hasSystemMessage($conversation));
    }

    public function testAddsSystemMessageSuccessfully(): void
    {
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);

        /** @var User $user */
        $user = $userFactory->create();

        /** @var ConversationFactory $conversationFactory */
        $conversationFactory = $this->getContainer()->get(ConversationFactory::class);

        /** @var Conversation $conversation */
        $conversation = $conversationFactory->create([
            'userEntity' => $user
        ]);

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);

        $chatService = new ChatService($messageRepository, $this->entityManager);

        $message = $chatService->addSystemMessage($conversation);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertGreaterThan(0, $message->getId());
        $this->assertInstanceOf(Conversation::class, $message->getConversation());
        $this->assertInstanceOf(MessageAuthorEnum::class, $message->getAuthor());
        $this->assertSame(MessageAuthorEnum::SYSTEM, $message->getAuthor());
        $this->assertIsString($message->getBody());
        $this->assertNotEmpty($message->getBody());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getUpdatedAt());
    }

    public function testAddsUserMessageSuccessfully(): void
    {
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);

        /** @var User $user */
        $user = $userFactory->create();

        /** @var ConversationFactory $conversationFactory */
        $conversationFactory = $this->getContainer()->get(ConversationFactory::class);

        /** @var Conversation $conversation */
        $conversation = $conversationFactory->create([
            'userEntity' => $user
        ]);

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);

        $chatService = new ChatService($messageRepository, $this->entityManager);

        $message = $chatService->addUserMessage($conversation, 'Hi, how are ya?');

        $this->assertInstanceOf(Message::class, $message);
        $this->assertGreaterThan(0, $message->getId());
        $this->assertInstanceOf(Conversation::class, $message->getConversation());
        $this->assertInstanceOf(MessageAuthorEnum::class, $message->getAuthor());
        $this->assertSame(MessageAuthorEnum::USER, $message->getAuthor());
        $this->assertIsString($message->getBody());
        $this->assertNotEmpty($message->getBody());
        $this->assertSame('Hi, how are ya?', $message->getBody());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getUpdatedAt());
    }

    public function testAddsAssistantMessageSuccessfully(): void
    {
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);

        /** @var User $user */
        $user = $userFactory->create();

        /** @var ConversationFactory $conversationFactory */
        $conversationFactory = $this->getContainer()->get(ConversationFactory::class);

        /** @var Conversation $conversation */
        $conversation = $conversationFactory->create([
            'userEntity' => $user
        ]);

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);

        $chatService = new ChatService($messageRepository, $this->entityManager);

        $message = $chatService->addAssistantMessage($conversation, 'Fine, what about ya?');

        $this->assertInstanceOf(Message::class, $message);
        $this->assertGreaterThan(0, $message->getId());
        $this->assertInstanceOf(Conversation::class, $message->getConversation());
        $this->assertInstanceOf(MessageAuthorEnum::class, $message->getAuthor());
        $this->assertSame(MessageAuthorEnum::ASSISTANT, $message->getAuthor());
        $this->assertIsString($message->getBody());
        $this->assertNotEmpty($message->getBody());
        $this->assertSame('Fine, what about ya?', $message->getBody());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getUpdatedAt());
    }

    public function testGetMessagesReturnsMessagesSuccessfully(): void
    {
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);

        /** @var User $user */
        $user = $userFactory->create();

        /** @var ConversationFactory $conversationFactory */
        $conversationFactory = $this->getContainer()->get(ConversationFactory::class);

        /** @var Conversation $conversation */
        $conversation = $conversationFactory->create([
            'userEntity' => $user
        ]);

        /** @var MessageFactory $messageFactory */
        $messageFactory = $this->getContainer()->get(MessageFactory::class);

        $message1 = $messageFactory->create([
            'conversation' => $conversation,
            'author' => MessageAuthorEnum::SYSTEM
        ]);

        $message2 = $messageFactory->create([
            'conversation' => $conversation,
            'author' => MessageAuthorEnum::USER
        ]);

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);

        $chatService = new ChatService($messageRepository, $this->entityManager);

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
        $this->assertSame($message1->getBody(), $messages[0]['content']);
        $this->assertSame($message2->getBody(), $messages[1]['content']);
    }

    public function testGetMessagesReturnsEmptyArrayWhenNoMessages(): void
    {
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);

        /** @var User $user */
        $user = $userFactory->create();

        /** @var ConversationFactory $conversationFactory */
        $conversationFactory = $this->getContainer()->get(ConversationFactory::class);

        /** @var Conversation $conversation */
        $conversation = $conversationFactory->create([
            'userEntity' => $user
        ]);

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);

        $chatService = new ChatService($messageRepository, $this->entityManager);

        $messages = $chatService->getMessages($conversation);

        $this->assertIsArray($messages);
        $this->assertCount(0, $messages);
    }
}
