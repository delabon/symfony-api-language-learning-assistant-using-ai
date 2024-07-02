<?php

namespace App\Tests\Feature\Message;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Factory\ConversationFactory;
use App\Factory\MessageFactory;
use App\Factory\UserFactory;
use App\Repository\MessageRepository;
use App\Tests\FeatureTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Response;

class ResetMessagesTest extends FeatureTestCase
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

        $messagesCountBefore = $this->messageRepository->count();

        $this->client->request(
            'POST',
            '/api/v1/message/reset/' . $this->conversation->getId(),
            server: [
                'HTTP_Authorization' => 'Bearer ' . $this->user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(1, $messagesCountBefore);
        $this->assertEquals(0, $this->messageRepository->count());
    }

    public function testResetsOnlyTheMessagesThatBelongsToConversation(): void
    {
        /** @var Conversation $conversation2 */
        $conversation2 = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'language' => LanguageEnum::Arabic,
            'userEntity' => $this->user
        ]);

        (new MessageFactory(Factory::create(), $this->entityManager))->create([
            'conversation' => $this->conversation
        ]);

        (new MessageFactory(Factory::create(), $this->entityManager))->create([
            'conversation' => $conversation2
        ]);

        $messagesCountBefore = $this->messageRepository->count();

        $this->client->request(
            'POST',
            '/api/v1/message/reset/' . $this->conversation->getId(),
            server: [
                'HTTP_Authorization' => 'Bearer ' . $this->user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(2, $messagesCountBefore);
        $this->assertEquals(1, $this->messageRepository->count());
    }

    public function testReturnsNotFoundResponseWhenNoConversationId(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/message/reset',
            server: [
                'HTTP_Authorization' => 'Bearer ' . $this->user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testReturnsBadRequestResponseWhenConversationIdIsZero(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/message/reset/0',
            server: [
                'HTTP_Authorization' => 'Bearer ' . $this->user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testReturnsNotFoundResponseWhenConversationIdDoesNotExist(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/message/reset/433',
            server: [
                'HTTP_Authorization' => 'Bearer ' . $this->user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testReturnsForbiddenResponseWhenNotTheOwner(): void
    {
        $user2 = (new UserFactory(Factory::create(), $this->entityManager))->create();

        $this->client->request(
            'POST',
            '/api/v1/message/reset/' . $this->conversation->getId(),
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user2->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testReturnsUnauthorizedResponseWhenNoApiKey(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/message/reset/' . $this->conversation->getId(),
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testReturnsUnauthorizedResponseWhenInvalidApiKey(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/message/reset/' . $this->conversation->getId(),
            server: [
                'HTTP_Authorization' => 'Bearer invalid key'
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
