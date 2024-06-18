<?php

namespace App\Tests;

use App\Tests\Trait\DatabaseResetter;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeatureTestCase extends WebTestCase
{
    use DatabaseResetter;

    protected ?KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        self::resetDb();

        parent::tearDown();
    }
}