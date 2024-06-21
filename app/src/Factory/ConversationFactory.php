<?php

namespace App\Factory;

use App\Entity\Conversation;
use App\Entity\User;
use App\Enum\LanguageEnum;
use DateTimeImmutable;

class ConversationFactory extends Factory
{
    protected string $entityClass = Conversation::class;

    protected function defaults(): array
    {
        return [
            'userEntity' => new User(), // make sure to override this
            'language' => LanguageEnum::random(),
            'createdAt' => DateTimeImmutable::createFromMutable($this->faker->dateTime()),
            'updatedAt' => DateTimeImmutable::createFromMutable($this->faker->dateTime()),
        ];
    }
}
