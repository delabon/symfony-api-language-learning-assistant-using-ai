<?php

namespace App\Tests\Trait;

use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\LanguageEnum;
use DateTimeImmutable;
use ReflectionClass;

trait ConversationCreator
{
    public function createConversation(?User $user= null, LanguageEnum $languageEnum = LanguageEnum::ITALIAN): Conversation
    {
        $user = $user ?: new User();
        $conversation = new Conversation();
        $conversation->setLanguage($languageEnum);
        $conversation->setUserEntity($user);
        $conversation->setCreatedAt(new DateTimeImmutable());
        $conversation->setUpdatedAt(new DateTimeImmutable());

        return $conversation;
    }

    public function createConversationWithId(int $id, ?User $user = null, LanguageEnum $languageEnum = LanguageEnum::ITALIAN): Conversation
    {
        $conversation = $this->createConversation($user, $languageEnum);

        $reflectionClass = new ReflectionClass($conversation);
        $prop = $reflectionClass->getProperty('id');
        $prop->setValue($conversation, $id);

        return $conversation;
    }
}