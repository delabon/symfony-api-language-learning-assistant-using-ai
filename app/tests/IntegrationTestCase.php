<?php

namespace App\Tests;

use App\Tests\Trait\DatabaseResetter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IntegrationTestCase extends KernelTestCase
{
    use DatabaseResetter;

    protected ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        self::resetDb();

        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        self::resetDb();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }
}