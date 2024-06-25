<?php

namespace App\Tests\Integration\Service;

use App\Doctrine\MessageAuthorEnum;
use App\Service\ChatGptService;
use App\Tests\IntegrationTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ChatGptServiceTest extends IntegrationTestCase
{
    public function testMethodCompletionsReturnsAiResponse(): void
    {
        $apiSecret = $this->getContainer()->getParameter('openai_secret');
        $client = HttpClient::create();

        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant. You are an English teacher.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'Can you help me with the past tenses?'
            ]
        ];

        $chatGptService = new ChatGptService($client, $apiSecret);

        $reply = $chatGptService->completions($messages);

        $this->assertIsString($reply);
        $this->assertGreaterThan(0, strlen($reply));
    }

    public function testMethodCompletionsReturnsUnauthorizedResponseWhenInvalidApiKey(): void
    {
        $apiSecret = 'Invalid api key is here!';
        $client = HttpClient::create();

        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant. You are an English teacher.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'Can you help me with the past tenses?'
            ]
        ];

        $chatGptService = new ChatGptService($client, $apiSecret);

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionCode(Response::HTTP_UNAUTHORIZED);
        $this->expectExceptionMessage('Invalid Authentication.');

        $chatGptService->completions($messages);
    }
}