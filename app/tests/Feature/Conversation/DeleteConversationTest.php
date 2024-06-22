<?php

namespace App\Tests\Feature\Conversation;

use App\Entity\Conversation;
use App\Entity\User;
use App\Factory\ConversationFactory;
use App\Factory\UserFactory;
use App\Tests\FeatureTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Response;

class DeleteConversationTest extends FeatureTestCase
{
    public function testDeletesConversationSuccessfully(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();
        /** @var Conversation $conversation */
        $conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'userEntity' => $user
        ]);

        $conversationRepository = $this->entityManager->getRepository(Conversation::class);
        $conversationsCountBefore = $conversationRepository->count();

        $this->client->request(
            'DELETE',
            '/api/v1/conversation/delete/' . $conversation->getId(),
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $conversations = $conversationRepository->findAll();
        $conversationsCountAfter = count($conversations);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertSame(1, $conversationsCountBefore);
        $this->assertSame(0, $conversationsCountAfter);
    }

    public function testReturnsForbiddenResponseWhenInvalidApiKey(): void
    {
        $this->client->request(
            'DELETE',
            '/api/v1/conversation/delete/344',
            server: [
                'HTTP_Authorization' => 'Bearer My Fake Api Key'
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testReturnsNotFoundResponseWhenNoId(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        $this->client->request(
            'DELETE',
            '/api/v1/conversation/delete',
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testReturnsBadRequestResponseWhenIdIsZero(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        $this->client->request(
            'DELETE',
            '/api/v1/conversation/delete/0',
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testReturnsForbiddenResponseWhenNonOwner(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();
        /** @var User $user */
        $user2 = (new UserFactory(Factory::create(), $this->entityManager))->create();

        /** @var Conversation $conversation */
        $conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'userEntity' => $user
        ]);

        $conversationRepository = $this->entityManager->getRepository(Conversation::class);
        $conversationsCountBefore = $conversationRepository->count();

        $this->client->request(
            'DELETE',
            '/api/v1/conversation/delete/' . $conversation->getId(),
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user2->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $conversationsCountAfter = count($conversationRepository->findAll());


        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame(1, $conversationsCountBefore);
        $this->assertSame(1, $conversationsCountAfter);
    }
}
