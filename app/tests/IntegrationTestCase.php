<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IntegrationTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
    }

    protected function tearDown(): void
    {
        $connection = static::getContainer()->get('doctrine')->getConnection();
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();

        foreach ($tables as $table) {
            $connection->executeStatement('TRUNCATE ' . $table . ' CASCADE');
        }

        parent::tearDown();
    }
}