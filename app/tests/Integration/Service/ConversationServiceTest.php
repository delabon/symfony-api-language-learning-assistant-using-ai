<?php

namespace App\Tests\Integration\Service;

use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Factory\ConversationFactory;
use App\Factory\UserFactory;
use App\Repository\ConversationRepository;
use App\Service\ConversationService;
use App\Tests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ConversationServiceTest extends IntegrationTestCase
{
    private ConversationRepository $conversationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // @phpstan-ignore-next-line
        $this->conversationRepository = $this->entityManager->getRepository(Conversation::class);
    }

    public function testGetsConversationSuccessfully(): void
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

        $conversationService = new ConversationService($this->conversationRepository);

        $conversationFetched = $conversationService->get($conversation->getId());

        $this->assertInstanceOf(Conversation::class, $conversationFetched);
        $this->assertSame($conversation->getId(), $conversationFetched->getId());
        $this->assertInstanceOf(LanguageEnum::class, $conversationFetched->getLanguage());
        $this->assertInstanceOf(User::class, $conversationFetched->getUserEntity());
    }

    public function testReturnsNotFoundJsonResponseWhenIdDoesNotExists(): void
    {
        $conversationId = 234;
        $conversationService = new ConversationService($this->conversationRepository);

        $result = $conversationService->get($conversationId);
        $json = json_decode($result->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertSame(Response::HTTP_NOT_FOUND, $result->getStatusCode());
        $this->assertIsArray($json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('conversation', $json['errors']);
        $this->assertSame('The conversation does not exist.', $json['errors']['conversation']);
    }
}
