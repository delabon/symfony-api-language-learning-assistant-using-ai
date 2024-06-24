<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\LanguageEnum;
use App\Serializer\Normalizer\ConversationNormalizer;
use App\Tests\Fake\FakeNormalizer;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class ConversationNormalizerTest extends TestCase
{
    public function testMethodNormalizeReturnsAnArrayOfDataSuccessfully(): void
    {
        $conversation = $this->createConversation();

        $normalizer = $this->createMock(FakeNormalizer::class);
        $normalizer->expects($this->once())
            ->method('normalize')
            ->with(
                $conversation,
                'json',
                [
                    'groups' => [
                        'conversations.list'
                    ]
                ]
            )
            ->willReturn([
                'id' => $conversation->getId(),
                'language' => $conversation->getLanguage()->value,
                'createdAt' => $conversation->getCreatedAt()->format('Y-m-d H-i-s'),
                'updatedAt' => $conversation->getUpdatedAt()->format('Y-m-d H-i-s'),
            ]);

        $conversationNormalizer = new ConversationNormalizer($normalizer);

        $result = $conversationNormalizer->normalize(
            $conversation,
            'json',
            [
                'groups' => [
                    'conversations.list'
                ]
            ]
        );

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

    public function testMethodSupportsNormalizationReturnsTrueWhenCorrectDataIsPassed(): void
    {
        $conversation = $this->createConversation();

        $normalizer = $this->createMock(FakeNormalizer::class);
        $conversationNormalizer = new ConversationNormalizer($normalizer);

        $result = $conversationNormalizer->supportsNormalization(
            $conversation,
            'json',
            [
                'groups' => [
                    'conversations.list'
                ]
            ]
        );

        $this->assertTrue($result);
    }

    public function testMethodSupportsNormalizationReturnsFalseWhenIncorrectDataIsPassed(): void
    {
        $normalizer = $this->createMock(FakeNormalizer::class);
        $conversationNormalizer = new ConversationNormalizer($normalizer);

        $result = $conversationNormalizer->supportsNormalization(
            new stdClass(),
            'json',
            [
                'groups' => [
                    'conversations.list'
                ]
            ]
        );

        $this->assertFalse($result);
    }

    public function testMethodDetSupportedTypesReturnsValidData(): void
    {
        $normalizer = $this->createMock(FakeNormalizer::class);
        $conversationNormalizer = new ConversationNormalizer($normalizer);

        $result = $conversationNormalizer->getSupportedTypes('json');

        $this->assertIsArray($result);
        $this->assertSame([Conversation::class => true], $result);
    }

    private function createConversation(): Conversation
    {
        $user = new User();
        $user->setName('John Doe');
        $user->setEmail('johndoe@example.com');
        $user->setUsername('johndoe');
        $userReflectionClass = new ReflectionClass($user);
        $prop = $userReflectionClass->getProperty('id');
        $prop->setValue($user, 34);

        $conversation = new Conversation();
        $conversation->setLanguage(LanguageEnum::FRENCH);
        $conversation->setCreatedAt(new DateTimeImmutable());
        $conversation->setUpdatedAt(new DateTimeImmutable());
        $conversation->setUserEntity($user);
        $conversationReflectionClass = new ReflectionClass($conversation);
        $prop = $conversationReflectionClass->getProperty('id');
        $prop->setValue($conversation, 99);

        return $conversation;
    }
}
