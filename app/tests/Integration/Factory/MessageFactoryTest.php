<?php

namespace App\Tests\Integration\Factory;

use App\Doctrine\MessageAuthorEnum;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Factory\ConversationFactory;
use App\Factory\Factory;
use App\Factory\FactoryInterface;
use App\Factory\MessageFactory;
use App\Factory\UserFactory;
use App\Tests\IntegrationTestCase;
use DateTimeImmutable;

class MessageFactoryTest extends IntegrationTestCase
{
    public function testCreatesMessageCorrectly(): void
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

        /** @var Message $message */
        $message = $messageFactory->create([
            'conversation' => $conversation
        ]);

        $this->assertInstanceOf(MessageFactory::class, $messageFactory);
        $this->assertInstanceOf(Factory::class, $messageFactory);
        $this->assertInstanceOf(FactoryInterface::class, $messageFactory);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertNotNull($message->getId());
        $this->assertGreaterThan(0, $message->getId());
        $this->assertInstanceOf(Conversation::class, $message->getConversation());
        $this->assertIsString($message->getBody());
        $this->assertNotEmpty($message->getBody());
        $this->assertInstanceOf(MessageAuthorEnum::class, $message->getAuthor());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getUpdatedAt());
    }
}
