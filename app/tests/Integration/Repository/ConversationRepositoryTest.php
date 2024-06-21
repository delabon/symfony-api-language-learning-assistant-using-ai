<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Factory\ConversationFactory;
use App\Factory\UserFactory;
use App\Repository\ConversationRepository;
use App\Tests\IntegrationTestCase;
use Faker\Factory;

class ConversationRepositoryTest extends IntegrationTestCase
{
    public function testFindByLanguageAndUserMethodReturnsAnArrayOfLanguagesSuccessfully(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'language' => LanguageEnum::Arabic,
            'userEntity' => $user
        ]);

        /** @var ConversationRepository $conversationRepository */
        $conversationRepository = $this->entityManager->getRepository(Conversation::class);

        $conversations = $conversationRepository->findByLanguageAndUser(LanguageEnum::Arabic, $user);

        $this->assertIsArray($conversations);
        $this->assertCount(1, $conversations);
        $this->assertGreaterThan(0, $conversations[0]->getId());
        $this->assertSame(LanguageEnum::Arabic, $conversations[0]->getLanguage());
    }

    public function testFindByLanguageMethodReturnsEmptyArrayWhenNoConversation(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        /** @var ConversationRepository $conversationRepository */
        $conversationRepository = $this->entityManager->getRepository(Conversation::class);

        /** @var Conversation[] $conversations */
        $conversations = $conversationRepository->findByLanguageAndUser(LanguageEnum::Arabic, $user);

        $this->assertIsArray($conversations);
        $this->assertCount(0, $conversations);
    }
}
