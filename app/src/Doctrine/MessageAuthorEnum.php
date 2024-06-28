<?php

namespace App\Doctrine;

enum MessageAuthorEnum: string
{
    case SYSTEM = 'system';
    case ASSISTANT = 'assistant';
    case USER = 'user';

    public static function random(): MessageAuthorEnum
    {
        return self::cases()[array_rand(self::cases())];
    }
}
