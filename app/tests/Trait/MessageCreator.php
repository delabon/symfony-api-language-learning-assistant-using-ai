<?php

namespace App\Tests\Trait;

use App\Doctrine\MessageAuthorEnum;
use App\Entity\Conversation;
use App\Entity\Message;
use DateTimeImmutable;
use ReflectionClass;

trait MessageCreator
{
    public function createMessage(string $body, MessageAuthorEnum $messageAuthorEnum = MessageAuthorEnum::USER, ?Conversation $conversation= null): Message
    {
        $conversation = $conversation ?: new Conversation();

        $message = new Message();
        $message->setConversation($conversation);
        $message->setBody($body);
        $message->setAuthor($messageAuthorEnum);
        $message->setCreatedAt(new DateTimeImmutable());
        $message->setUpdatedAt(new DateTimeImmutable());

        return $message;
    }

    public function createMessageWithId(int $id, string $body, MessageAuthorEnum $messageAuthorEnum = MessageAuthorEnum::USER, ?Conversation $conversation= null): Message
    {
        $message = $this->createMessage($body, $messageAuthorEnum, $conversation);

        $reflectionClass = new ReflectionClass($message);
        $prop = $reflectionClass->getProperty('id');
        $prop->setValue($message, $id);

        return $message;
    }
}