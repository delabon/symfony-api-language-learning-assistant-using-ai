<?php

namespace App\Service;

use App\Entity\User;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ApiKeyGenerator
{
    public function __construct(
        #[Autowire('%app_secret%')]
        private string $appSecret
    ) {
    }

    public function generate(User $user): string
    {
        if (empty($user->getEmail())) {
            throw new InvalidArgumentException('Invalid email.');
        }

        if (empty($user->getUsername())) {
            throw new InvalidArgumentException('Invalid username.');
        }

        return hash('sha512', uniqid() . $user->getEmail() . $user->getUsername() . $this->appSecret);
    }
}
