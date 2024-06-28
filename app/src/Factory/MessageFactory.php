<?php

namespace App\Factory;

use App\Doctrine\MessageAuthorEnum;
use App\Entity\Conversation;
use App\Entity\Message;
use DateTimeImmutable;

class MessageFactory extends Factory
{
    protected string $entityClass = Message::class;

    protected function defaults(): array
    {
        return [
            'conversation' => new Conversation(), // make sure to override this
            'author' => MessageAuthorEnum::random(),
            'body' => $this->faker->text(500),
            'createdAt' => DateTimeImmutable::createFromMutable($this->faker->dateTime()),
            'updatedAt' => DateTimeImmutable::createFromMutable($this->faker->dateTime()),
        ];
    }
}
