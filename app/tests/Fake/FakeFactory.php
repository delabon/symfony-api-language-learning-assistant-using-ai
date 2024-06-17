<?php

namespace App\Tests\Fake;

use App\Factory\Factory;

class FakeFactory extends Factory
{
    protected string $entityClass = FakeEntity::class;

    protected function defaults(): array
    {
        return [
            'name' => $this->faker->name(),
            'apiKey' => $this->faker->text(118),
        ];
    }
}