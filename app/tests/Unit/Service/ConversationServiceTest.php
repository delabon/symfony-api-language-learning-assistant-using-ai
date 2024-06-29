<?php

namespace App\Tests\Unit\Service;

use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Repository\ConversationRepository;
use App\Service\ConversationService;
use App\Tests\UnitTestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ConversationServiceTest extends UnitTestCase
{
    public function testGetsConversationSuccessfully(): void
    {
        $conversationId = 344;
        $conversationRepoMock = $this->createMock(ConversationRepository::class);
        $conversationRepoMock->expects($this->once())
            ->method('find')
            ->with($conversationId)
            ->willReturnCallback(function ($id) {
                $conversation = new Conversation();
                $conversation->setLanguage(LanguageEnum::Arabic);
                $conversation->setUserEntity(new User());
                $reflectionClass = new ReflectionClass($conversation);
                $property = $reflectionClass->getProperty('id');
                $property->setValue($conversation, $id);

                return $conversation;
            });

        $conversationService = new ConversationService($conversationRepoMock);

        $conversationFetched = $conversationService->get($conversationId);

        $this->assertInstanceOf(Conversation::class, $conversationFetched);
        $this->assertSame($conversationId, $conversationFetched->getId());
        $this->assertSame(LanguageEnum::Arabic, $conversationFetched->getLanguage());
        $this->assertInstanceOf(User::class, $conversationFetched->getUserEntity());
    }

    public function testReturnsBadRequestJsonResponseWhenInvalidId(): void
    {
        $conversationId = 0;
        $conversationRepoMock = $this->createStub(ConversationRepository::class);
        $conversationService = new ConversationService($conversationRepoMock);

        $result = $conversationService->get($conversationId);
        $json = json_decode($result->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $result->getStatusCode());
        $this->assertIsArray($json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('conversation', $json['errors']);
        $this->assertSame('Invalid conversation id.', $json['errors']['conversation']);
    }

    public function testReturnsNotFoundJsonResponseWhenIdDoesNotExists(): void
    {
        $conversationId = 234;
        $conversationRepoMock = $this->createStub(ConversationRepository::class);
        $conversationService = new ConversationService($conversationRepoMock);

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
