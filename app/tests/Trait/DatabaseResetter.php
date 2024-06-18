<?php

namespace App\Tests\Trait;

trait DatabaseResetter
{
    public static function resetDb(): void
    {
        $connection = static::getContainer()->get('doctrine')->getConnection();
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();

        foreach ($tables as $table) {
            $connection->executeStatement('TRUNCATE ' . $table . ' CASCADE');
        }
    }
}