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

class CreateConversationTest extends FeatureTestCase
{
    public function testCreatesConversationSuccessfully(): void
    {
        $conversationRepository = $this->entityManager->getRepository(Conversation::class);
        $conversationsCountBefore = $conversationRepository->count();

        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();
        $this->client->request(
            'POST',
            '/api/v1/conversation/create',
            parameters: [
                'language' => 'English',
            ],
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $conversations = $conversationRepository->findAll();
        $conversationsCountAfter = count($conversations);
        /** @var User $conversationUser */
        $conversationUser = $conversations[0]->getUserEntity();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('id', $result);
        $this->assertGreaterThan(0, $result['id']);
        $this->assertEquals($conversationsCountBefore + 1, $conversationsCountAfter);
        $this->assertSame($conversations[0]->getId(), $result['id']);
        $this->assertSame($conversationUser->getId(), $user->getId());
        $this->assertSame($conversations[0]->getLanguage(), LanguageEnum::ENGLISH);
    }

    public function testReturnsUnauthorizedWhenCreatingConversationWithoutAuthorizationHeader(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/conversation/create',
            parameters: [
                'language' => 'English',
            ],
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testReturnsUnauthorizedWhenCreatingConversationWithInvalidApiKeyOrDoesNotExist(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/conversation/create',
            parameters: [
                'language' => 'English',
            ],
            server: [
                'HTTP_Authorization' => 'Bearer ' . 'invalid api key or does not exist'
            ]
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testReturnsBadRequestWhenCreatingConversationWithInvalidOrUnsupportedLanguage(): void
    {
        $conversationRepository = $this->entityManager->getRepository(Conversation::class);
        $conversationsCountBefore = $conversationRepository->count();

        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        $this->client->request(
            'POST',
            '/api/v1/conversation/create',
            parameters: [
                'language' => 'Russian',
            ],
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $conversations = $conversationRepository->findAll();
        $conversationsCountAfter = count($conversations);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('Invalid or unsupported language.', $result['error']);
        $this->assertSame($conversationsCountBefore, $conversationsCountAfter);
    }

    public function testReturnsForbiddenWhenCreatingConversationWithLanguageThatAlreadyExists(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'language' => LanguageEnum::ENGLISH,
            'userEntity' => $user
        ]);

        $conversationRepository = $this->entityManager->getRepository(Conversation::class);
        $conversationsCountBefore = $conversationRepository->count();

        $this->client->request(
            'POST',
            '/api/v1/conversation/create',
            parameters: [
                'language' => LanguageEnum::ENGLISH->value,
            ],
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $conversations = $conversationRepository->findAll();
        $conversationsCountAfter = count($conversations);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('A conversation with English the language already exists.', $result['error']);
        $this->assertSame($conversationsCountBefore, $conversationsCountAfter);
    }
}