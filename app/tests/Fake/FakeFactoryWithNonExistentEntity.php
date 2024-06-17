<?php

namespace App\Tests\Fake;

use App\Factory\Factory;

class FakeFactoryWithNonExistentEntity extends Factory
{
    protected string $entityClass = 'DoesNotExist';

    public static function class(): string
    {
        return 'DoeNotExistEntity';
    }

    protected function defaults(): array
    {
        return [];
    }
}