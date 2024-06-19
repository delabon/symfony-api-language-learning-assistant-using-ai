<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Service\ApiKeyGenerator;
use App\Tests\UnitTestCase;

class ApiKeyGeneratorTest extends UnitTestCase
{
    public function testGeneratesApiKeySuccessfully(): void
    {
        $user = new User();
        $user->setUsername('johndoe');
        $user->setName('John Doe');
        $user->setEmail('johndoe@example.com');

        $generator = new ApiKeyGenerator('MyFakeSecret');

        $apiKey = $generator->generate($user);

        $this->assertIsString($apiKey);
        $this->assertEquals(128, strlen($apiKey));
    }

    public function testThrowsExceptionWhenEmptyEmail(): void
    {
        $user = new User();
        $user->setUsername('johndoe');
        $user->setName('John Doe');

        $generator = new ApiKeyGenerator('MyFakeSecret');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email.');

        $generator->generate($user);
    }

    public function testThrowsExceptionWhenEmptyUsername(): void
    {
        $user = new User();
        $user->setName('John Doe');
        $user->setEmail('johndoe@example.com');

        $generator = new ApiKeyGenerator('MyFakeSecret');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid username.');

        $generator->generate($user);
    }
}