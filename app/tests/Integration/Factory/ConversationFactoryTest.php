<?php

namespace App\Tests\Integration\Factory;

use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Factory\ConversationFactory;
use App\Factory\Factory;
use App\Factory\FactoryInterface;
use App\Factory\UserFactory;
use App\Tests\IntegrationTestCase;
use DateTimeImmutable;

class ConversationFactoryTest extends IntegrationTestCase
{
    public function testCreatesConversationCorrectly(): void
    {
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);

        /** @var ConversationFactory $conversationFactory */
        $conversationFactory = $this->getContainer()->get(ConversationFactory::class);

        /** @var Conversation $conversation */
        $conversation = $conversationFactory->create([
            'userEntity' => $userFactory->create()
        ]);

        $this->assertInstanceOf(ConversationFactory::class, $conversationFactory);
        $this->assertInstanceOf(Factory::class, $conversationFactory);
        $this->assertInstanceOf(FactoryInterface::class, $conversationFactory);
        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertGreaterThan(0, $conversation->getId());
        $this->assertNotEmpty($conversation->getLanguage());
        $this->assertInstanceOf(LanguageEnum::class, $conversation->getLanguage());
        $this->assertInstanceOf(User::class, $conversation->getUserEntity());
        $this->assertGreaterThan(0, $conversation->getUserEntity()->getId());
        $this->assertInstanceOf(DateTimeImmutable::class, $conversation->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $conversation->getUpdatedAt());
    }
}
