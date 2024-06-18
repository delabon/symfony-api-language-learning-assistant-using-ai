<?php

namespace App\Tests\Integration\Factory;

use App\Entity\User;
use App\Factory\Factory;
use App\Factory\FactoryInterface;
use App\Factory\UserFactory;
use App\Tests\IntegrationTestCase;

class UserFactoryTest extends IntegrationTestCase
{
    public function testCreatesUserCorrectly(): void
    {
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);

        $user = $userFactory->create();

        $this->assertInstanceOf(UserFactory::class, $userFactory);
        $this->assertInstanceOf(Factory::class, $userFactory);
        $this->assertInstanceOf(FactoryInterface::class, $userFactory);
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->getId());
        $this->assertNotEmpty($user->getName());
        $this->assertLessThanOrEqual(255, strlen($user->getName()));
        $this->assertNotEmpty($user->getApiKey());
        $this->assertLessThanOrEqual(255, strlen($user->getApiKey()));
    }
}
