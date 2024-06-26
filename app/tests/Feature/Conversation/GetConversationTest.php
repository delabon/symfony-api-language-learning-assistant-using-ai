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

class GetConversationTest extends FeatureTestCase
{
    public function testGetsConversationSuccessfully(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        /** @var Conversation $conversation */
        $conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'userEntity' => $user,
            'language' => LanguageEnum::Arabic
        ]);

        $this->client->request(
            'GET',
            '/api/v1/conversation/' . $conversation->getId(),
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($result);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('language', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayNotHasKey('userEntity', $result);
        $this->assertArrayNotHasKey('user_entity', $result);
        $this->assertSame($conversation->getId(), $result['id']);
        $this->assertSame($conversation->getLanguage()->value, $result['language']);
        $this->assertSame($conversation->getCreatedAt()->format('Y-m-d H-i-s'), $result['created_at']);
        $this->assertSame($conversation->getUpdatedAt()->format('Y-m-d H-i-s'), $result['updated_at']);
    }

    public function testReturnsNotFoundResponseWhenNoId(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        $this->client->request(
            'GET',
            '/api/v1/conversation/',
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testReturnsNotFoundResponseWhenIdDoesNotExist(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        $this->client->request(
            'GET',
            '/api/v1/conversation/34243424',
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testReturnsUnauthorizedResponseWhenNoApiKey(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        /** @var Conversation $conversation */
        $conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'userEntity' => $user,
            'language' => LanguageEnum::Arabic
        ]);

        $this->client->request(
            'GET',
            '/api/v1/conversation/' . $conversation->getId(),
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testReturnsUnauthorizedResponseWhenInvalidApiKey(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        /** @var Conversation $conversation */
        $conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'userEntity' => $user,
            'language' => LanguageEnum::Arabic
        ]);

        $this->client->request(
            'GET',
            '/api/v1/conversation/' . $conversation->getId(),
            server: [
                'HTTP_Authorization' => 'Bearer Fake API Key'
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testReturnsForbiddenResponseWhenNotTheOwner(): void
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

        $this->client->request(
            'GET',
            '/api/v1/conversation/' . $conversation->getId(),
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user2->getApiKey()
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
}
