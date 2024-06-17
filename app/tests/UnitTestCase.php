<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{
    public function assertEmail(string $email): void
    {
        $this->assertNotEmpty($email);
        $this->assertTrue(filter_var($email, FILTER_VALIDATE_EMAIL) !== false, 'Invalid email format');
    }
}