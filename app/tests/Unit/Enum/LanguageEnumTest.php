<?php

namespace App\Tests\Unit\Enum;

use App\Enum\LanguageEnum;
use App\Tests\UnitTestCase;

class LanguageEnumTest extends UnitTestCase
{
    public function testFindEnumByStringSuccessfully(): void
    {
        $this->assertSame(LanguageEnum::ENGLISH, LanguageEnum::find('english'));
        $this->assertSame(LanguageEnum::Arabic, LanguageEnum::find('ArAbic'));
    }

    public function testFindMethodReturnsNullWhenInvalidOrUnsupportedLanguage(): void
    {
        $this->assertNull(LanguageEnum::find('my invalid language haha'));
        $this->assertNull(LanguageEnum::find('Ainu'));
    }
}
