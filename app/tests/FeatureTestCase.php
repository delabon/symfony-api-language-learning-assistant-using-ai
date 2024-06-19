<?php

namespace App\Tests;

use App\Tests\Trait\DatabaseResetter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeatureTestCase extends WebTestCase
{
    use DatabaseResetter;

    protected ?KernelBrowser $client;
    protected ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        self::resetDb(); // In case of an error happened when creating a test, we may not arrive to execute the tearDown method
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