<?php

namespace App\Tests\Unit\Factory;

use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Factory\ConversationFactory;
use App\Factory\Factory;
use App\Factory\FactoryInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as Faker;
use PHPUnit\Framework\TestCase;

class ConversationFactoryTest extends TestCase
{
    public function testMakesConversationSuccessfully(): void
    {
        $conversationFactory = new ConversationFactory(Faker::create(), $this->createStub(EntityManagerInterface::class));

        $conversation = $conversationFactory->make();

        $this->assertInstanceOf(ConversationFactory::class, $conversationFactory);
        $this->assertInstanceOf(Factory::class, $conversationFactory);
        $this->assertInstanceOf(FactoryInterface::class, $conversationFactory);
        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertNull($conversation->getId());
        $this->assertNotEmpty($conversation->getLanguage());
        $this->assertInstanceOf(LanguageEnum::class, $conversation->getLanguage());
        $this->assertInstanceOf(User::class, $conversation->getUserEntity());
        $this->assertInstanceOf(DateTimeImmutable::class, $conversation->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $conversation->getUpdatedAt());
    }
}
