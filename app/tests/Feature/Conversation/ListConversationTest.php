<?php

namespace App\Tests\Feature\Conversation;

use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Factory\ConversationFactory;
use App\Factory\UserFactory;
use App\Tests\FeatureTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Response;

class ListConversationTest extends FeatureTestCase
{
    public function testListsConversationsSuccessfully(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        /** @var Conversation $conversation */
        $conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'userEntity' => $user,
            'language' => LanguageEnum::Arabic
        ]);

        /** @var Conversation $conversation2 */
        $conversation2 = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'userEntity' => $user,
            'language' => LanguageEnum::ITALIAN
        ]);

        $this->client->request(
            'GET',
            '/api/v1/conversation/list',
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('language', $result[0]);
        $this->assertArrayHasKey('created_at', $result[0]);
        $this->assertArrayHasKey('updated_at', $result[0]);
        $this->assertArrayNotHasKey('userEntity', $result[0]);
        $this->assertArrayNotHasKey('user_entity', $result[0]);
        $this->assertSame($conversation->getId(), $result[0]['id']);
        $this->assertSame($conversation2->getId(), $result[1]['id']);
        $this->assertSame($conversation->getLanguage()->value, $result[0]['language']);
        $this->assertSame($conversation2->getLanguage()->value, $result[1]['language']);
        $this->assertSame($conversation->getCreatedAt()->format('Y-m-d H-i-s'), $result[0]['created_at']);
        $this->assertSame($conversation->getUpdatedAt()->format('Y-m-d H-i-s'), $result[0]['updated_at']);
    }

    public function testListsNoConversationsWhenUserHasNoConversations(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        $this->client->request(
            'GET',
            '/api/v1/conversation/list',
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testListsOnlyTheUsersConversationsSuccessfully(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();
        /** @var User $user2 */
        $user2 = (new UserFactory(Factory::create(), $this->entityManager))->create();

        /** @var Conversation $conversation */
        $conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'userEntity' => $user,
            'language' => LanguageEnum::Arabic
        ]);

        /** @var Conversation $conversation2 */
        $conversation2 = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'userEntity' => $user2,
            'language' => LanguageEnum::ITALIAN
        ]);

        $this->client->request(
            'GET',
            '/api/v1/conversation/list',
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame($conversation->getId(), $result[0]['id']);
    }

    public function testReturnsUnauthorizedWhenNoApiKey(): void
    {
        $this->client->request(
            'GET',
            '/api/v1/conversation/list',
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testReturnsUnauthorizedWhenInvalidApiKey(): void
    {
        $this->client->request(
            'GET',
            '/api/v1/conversation/list',
            server: [
                'HTTP_Authorization' => 'Bearer Invalid Api Key'
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
