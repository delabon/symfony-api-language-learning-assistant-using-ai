<?php

namespace App\Doctrine;

enum MessageAuthorEnum: string
{
    case SYSTEM = 'system';
    case ASSISTANT = 'assistant';
    case USER = 'user';
}
