<?php

namespace App\Tests\Unit\Enum;

use App\Doctrine\MessageAuthorEnum;
use App\Tests\UnitTestCase;

class MessageAuthorEnumTest extends UnitTestCase
{
    public function testRandomMethodReturnsRandomAuthor(): void
    {
        $author = MessageAuthorEnum::random();

        $this->assertNotNull($author);
        $this->assertInstanceOf(MessageAuthorEnum::class, $author);
    }
}
