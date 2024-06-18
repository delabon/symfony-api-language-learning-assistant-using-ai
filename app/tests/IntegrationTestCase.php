<?php

namespace App\Tests;

use App\Tests\Trait\DatabaseResetter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IntegrationTestCase extends KernelTestCase
{
    use DatabaseResetter;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
    }

    protected function tearDown(): void
    {
        self::resetDb();

        parent::tearDown();
    }
}