<?php

namespace App\Tests\Unit\Factory;

use App\Doctrine\MessageAuthorEnum;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Factory\Factory;
use App\Factory\FactoryInterface;
use App\Factory\MessageFactory;
use App\Tests\UnitTestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as Faker;

class MessageFactoryTest extends UnitTestCase
{
    public function testMakesMessageCorrectly(): void
    {
        $messageFactory = new MessageFactory(Faker::create(), $this->createStub(EntityManagerInterface::class));

        /** @var Message $message */
        $message = $messageFactory->create();

        $this->assertInstanceOf(MessageFactory::class, $messageFactory);
        $this->assertInstanceOf(Factory::class, $messageFactory);
        $this->assertInstanceOf(FactoryInterface::class, $messageFactory);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertNull($message->getId());
        $this->assertIsString($message->getBody());
        $this->assertNotEmpty($message->getBody());
        $this->assertInstanceOf(Conversation::class, $message->getConversation());
        $this->assertInstanceOf(MessageAuthorEnum::class, $message->getAuthor());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getUpdatedAt());
    }
}
