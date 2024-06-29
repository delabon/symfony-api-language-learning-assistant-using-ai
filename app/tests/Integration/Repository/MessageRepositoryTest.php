<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Factory\ConversationFactory;
use App\Factory\MessageFactory;
use App\Factory\UserFactory;
use App\Repository\MessageRepository;
use App\Tests\IntegrationTestCase;
use Faker\Factory;

class MessageRepositoryTest extends IntegrationTestCase
{
    private User $user;
    private Conversation $conversation;
    private MessageRepository $messageRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = (new UserFactory(Factory::create(), $this->entityManager))->create();
        $this->conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'language' => LanguageEnum::Arabic,
            'userEntity' => $this->user
        ]);

        /** @phpstan-ignore-next-line */
        $this->messageRepository = $this->entityManager->getRepository(Message::class);
    }

    public function testResetsMessagesSuccessfully(): void
    {
        (new MessageFactory(Factory::create(), $this->entityManager))->create([
            'conversation' => $this->conversation
        ]);

        (new MessageFactory(Factory::create(), $this->entityManager))->create([
            'conversation' => $this->conversation
        ]);

        $messagesCountBefore = $this->messageRepository->count();

        $this->messageRepository->reset($this->conversation);

        $this->assertSame(2, $messagesCountBefore);
        $this->assertSame(0, $this->messageRepository->count());
    }

    public function testResetsOnlyTheMessagesThatBelongsToConversation(): void
    {
        (new MessageFactory(Factory::create(), $this->entityManager))->create([
            'conversation' => $this->conversation
        ]);

        /** @var Conversation $conversation2 */
        $conversation2 = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'language' => LanguageEnum::FRENCH,
            'userEntity' => $this->user
        ]);

        (new MessageFactory(Factory::create(), $this->entityManager))->create([
            'conversation' => $conversation2
        ]);

        $messagesCountBefore = $this->messageRepository->count();

        $this->messageRepository->reset($this->conversation);

        $this->assertSame(2, $messagesCountBefore);
        $this->assertSame(1, $this->messageRepository->count());
    }
}
