<?php

namespace App\Factory;

use App\Entity\User;
use DateTimeImmutable;

class UserFactory extends Factory
{
    protected string $entityClass = User::class;

    protected function defaults(): array
    {
        return [
            'apiKey' => $this->faker->text(118),
            'email' => $this->faker->email(),
            'name' => $this->faker->name(),
            'username' => $this->faker->text(50),
            'createdAt' => DateTimeImmutable::createFromMutable($this->faker->dateTime()),
            'updatedAt' => DateTimeImmutable::createFromMutable($this->faker->dateTime()),
        ];
    }
}
