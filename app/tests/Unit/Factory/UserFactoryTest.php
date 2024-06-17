<?php

namespace App\Tests\Unit\Factory;

use App\Entity\User;
use App\Factory\Factory;
use App\Factory\FactoryInterface;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as Faker;
use PHPUnit\Framework\TestCase;

class UserFactoryTest extends TestCase
{
    public function testMakesUserCorrectly(): void
    {
        /** @var UserFactory $userFactory */
        $userFactory = new UserFactory(Faker::create(), $this->createStub(EntityManagerInterface::class));

        $user = $userFactory->create();

        $this->assertInstanceOf(UserFactory::class, $userFactory);
        $this->assertInstanceOf(Factory::class, $userFactory);
        $this->assertInstanceOf(FactoryInterface::class, $userFactory);
        $this->assertInstanceOf(User::class, $user);
        $this->assertNull($user->getId());
        $this->assertNotEmpty($user->getName());
        $this->assertLessThanOrEqual(255, strlen($user->getName()));
        $this->assertNotEmpty($user->getApiKey());
        $this->assertLessThanOrEqual(255, strlen($user->getApiKey()));
    }
}
