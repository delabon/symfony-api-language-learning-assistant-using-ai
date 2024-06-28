<?php

namespace App\Tests\Feature\Message;

use App\Doctrine\MessageAuthorEnum;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Factory\ConversationFactory;
use App\Factory\UserFactory;
use App\Tests\FeatureTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Response;

class ChatWithAiTest extends FeatureTestCase
{
    public function testChatsWithAiSuccessfully(): void
    {
        $messageRepository = $this->entityManager->getRepository(Message::class);

        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        /** @var Conversation $conversation */
        $conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'language' => LanguageEnum::ENGLISH,
            'userEntity' => $user
        ]);

        $body = 'Hello, how are you today?';

        $this->client->request(
            'POST',
            '/api/v1/message/create',
            parameters: [
                'conversation_id' => $conversation->getId(),
                'body' => $body
            ],
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('body', $result);
        $this->assertGreaterThan(0, $result['id']);
        $this->assertIsString($result['body']);
        $this->assertNotEmpty($result['body']);
        $this->assertNotSame($body, $result['body']);

        $messages = $messageRepository->findAll();

        $this->assertIsArray($messages);
        $this->assertCount(3, $messages);

        // Validate message 1
        $this->assertSame($messages[0]->getConversation()->getId(), $conversation->getId());
        $this->assertSame('You are a helpful assistant. You are a language teacher and the language you are helping me with is ' . LanguageEnum::ENGLISH->value . '.', $messages[0]->getBody());
        $this->assertSame(MessageAuthorEnum::SYSTEM, $messages[0]->getAuthor());

        // Validate message 2
        $this->assertSame($conversation->getId(), $messages[1]->getConversation()->getId());
        $this->assertSame($body, $messages[1]->getBody());
        $this->assertSame(MessageAuthorEnum::USER, $messages[1]->getAuthor());

        // Validate message 3
        $this->assertSame($messages[2]->getId(), $result['id']);
        $this->assertSame($conversation->getId(), $messages[2]->getConversation()->getId());
        $this->assertSame($messages[2]->getBody(), $result['body']);
        $this->assertSame(MessageAuthorEnum::ASSISTANT, $messages[2]->getAuthor());
    }

    public function testReturnsBadRequestResponseWhenNoBody(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        /** @var Conversation $conversation */
        $conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'language' => LanguageEnum::ENGLISH,
            'userEntity' => $user
        ]);

        $this->client->request(
            'POST',
            '/api/v1/message/create',
            parameters: [
                'conversation_id' => $conversation->getId(),
            ],
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('body', $result['errors']);
        $this->assertSame('Invalid message body.', $result['errors']['body']);
    }

    public function testReturnsBadRequestResponseWhenEmptyBody(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        /** @var Conversation $conversation */
        $conversation = (new ConversationFactory(Factory::create(), $this->entityManager))->create([
            'language' => LanguageEnum::ENGLISH,
            'userEntity' => $user
        ]);

        $this->client->request(
            'POST',
            '/api/v1/message/create',
            parameters: [
                'conversation_id' => $conversation->getId(),
                'body' => '',
            ],
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('body', $result['errors']);
        $this->assertSame('Invalid message body.', $result['errors']['body']);
    }

    public function testReturnsBadRequestResponseWhenNoConversationId(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        $this->client->request(
            'POST',
            '/api/v1/message/create',
            parameters: [
                'body' => 'Cool day today.'
            ],
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('conversation', $result['errors']);
        $this->assertSame('Invalid conversation id.', $result['errors']['conversation']);
    }

    public function testReturnsBadRequestResponseWhenConversationIdIsZero(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        $this->client->request(
            'POST',
            '/api/v1/message/create',
            parameters: [
                'conversation_id' => 0,
                'body' => 'Cool day today.'
            ],
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('conversation', $result['errors']);
        $this->assertSame('Invalid conversation id.', $result['errors']['conversation']);
    }

    public function testReturnsNotFoundResponseWhenConversationIdDoesNotExist(): void
    {
        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create();

        $this->client->request(
            'POST',
            '/api/v1/message/create',
            parameters: [
                'conversation_id' => 324,
                'body' => 'Cool day today.'
            ],
            server: [
                'HTTP_Authorization' => 'Bearer ' . $user->getApiKey()
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('conversation', $result['errors']);
        $this->assertSame('The conversation does not exist.', $result['errors']['conversation']);
    }
}
