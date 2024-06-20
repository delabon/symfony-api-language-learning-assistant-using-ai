<?php

namespace App\Enum;

enum LanguageEnum: string
{
    case ENGLISH = 'English';
    case FRENCH = 'French';
    case ITALIAN = 'Italian';
    case Arabic = 'Arabic';

    public static function find(string $language): ?LanguageEnum
    {
        $language = strtolower($language);

        foreach (self::cases() as $case) {
            if (strtolower($case->value) === $language) {
                return $case;
            }
        }

        return null;
    }
}
