<?php

namespace App\Tests\Unit\Service;

use App\Doctrine\MessageAuthorEnum;
use App\Exception\ApiServerErrorException;
use App\Exception\ApiServerIsOverloadedException;
use App\Exception\RateLimitException;
use App\Exception\UnsupportedRegionException;
use App\Service\ChatGptService;
use App\Tests\Fake\FakeHttpClient;
use App\Tests\Fake\FakeHttpClientResponse;
use App\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use UnexpectedValueException;

class ChatGptServiceTest extends UnitTestCase
{
    public function testMethodCompletionsReturnsAiResponse(): void
    {
        $fakeApiKey = 'My Api Key';
        $aiReply = 'Good, What about you?';
        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'How are you today?'
            ]
        ];
        $httpClientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $httpClientResponseMock->expects($this->once())
            ->method('getContent')
            ->with(false)
            ->willReturn(json_encode(
                [
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => $aiReply
                            ]
                        ]
                    ]
                ]
            ));

        $httpClientMock = $this->createMock(FakeHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        0 => 'Content-Type: application/json',
                        1 => 'Authorization: Bearer ' . $fakeApiKey
                    ],
                    'body' => json_encode([
                        'messages' => $messages,
                        'model' => 'gpt-4'
                    ]),
                ],
            )->willReturn($httpClientResponseMock);

        $chatGptService = new ChatGptService($httpClientMock, $fakeApiKey);

        $reply = $chatGptService->completions($messages);

        $this->assertSame($aiReply, $reply);
    }

    public function testMethodCompletionsReturnsUnauthorizedResponseWhenNoApiKey(): void
    {
        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'How are you today?'
            ]
        ];
        $httpClientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $httpClientResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_UNAUTHORIZED);

        $httpClientMock = $this->createMock(FakeHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        0 => 'Content-Type: application/json',
                        1 => 'Authorization: Bearer '
                    ],
                    'body' => json_encode([
                        'messages' => $messages,
                        'model' => 'gpt-4'
                    ]),
                ],
            )->willReturn($httpClientResponseMock);

        $chatGptService = new ChatGptService($httpClientMock, '');

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionCode(Response::HTTP_UNAUTHORIZED);
        $this->expectExceptionMessage('Invalid Authentication.');

        $chatGptService->completions($messages);
    }

    public function testMethodCompletionsReturnsUnauthorizedResponseWhenInvalidApiKey(): void
    {
        $fakeApiKey = 'this is an invalid api key';
        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'How are you today?'
            ]
        ];
        $httpClientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $httpClientResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_UNAUTHORIZED);

        $httpClientMock = $this->createMock(FakeHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        0 => 'Content-Type: application/json',
                        1 => 'Authorization: Bearer ' . $fakeApiKey
                    ],
                    'body' => json_encode([
                        'messages' => $messages,
                        'model' => 'gpt-4'
                    ]),
                ],
            )->willReturn($httpClientResponseMock);

        $chatGptService = new ChatGptService($httpClientMock, $fakeApiKey);

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionCode(Response::HTTP_UNAUTHORIZED);
        $this->expectExceptionMessage('Invalid Authentication.');

        $chatGptService->completions($messages);
    }

    public function testMethodCompletionsReturnsForbiddenResponseCountryRegionOrTerritoryIsNoSupported(): void
    {
        $fakeApiKey = 'This is a valid api key';
        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'How are you today?'
            ]
        ];
        $httpClientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $httpClientResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_FORBIDDEN);

        $httpClientMock = $this->createMock(FakeHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        0 => 'Content-Type: application/json',
                        1 => 'Authorization: Bearer ' . $fakeApiKey
                    ],
                    'body' => json_encode([
                        'messages' => $messages,
                        'model' => 'gpt-4'
                    ]),
                ],
            )->willReturn($httpClientResponseMock);

        $chatGptService = new ChatGptService($httpClientMock, $fakeApiKey);

        $this->expectException(UnsupportedRegionException::class);
        $this->expectExceptionCode(Response::HTTP_FORBIDDEN);
        $this->expectExceptionMessage('Country, region, or territory not supported.');

        $chatGptService->completions($messages);
    }

    public function testMethodCompletionsReturnsTooManyRequestsResponseWhenRateLimitReached(): void
    {
        $fakeApiKey = 'This is a valid api key';
        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'How are you today?'
            ]
        ];
        $httpClientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $httpClientResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_TOO_MANY_REQUESTS);

        $httpClientMock = $this->createMock(FakeHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        0 => 'Content-Type: application/json',
                        1 => 'Authorization: Bearer ' . $fakeApiKey
                    ],
                    'body' => json_encode([
                        'messages' => $messages,
                        'model' => 'gpt-4'
                    ]),
                ],
            )->willReturn($httpClientResponseMock);

        $chatGptService = new ChatGptService($httpClientMock, $fakeApiKey);

        $this->expectException(RateLimitException::class);
        $this->expectExceptionCode(Response::HTTP_TOO_MANY_REQUESTS);
        $this->expectExceptionMessage('Rate limit reached for requests.');

        $chatGptService->completions($messages);
    }

    public function testMethodCompletionsReturnsServerErrorResponseSomethingWrongWithTheApiServer(): void
    {
        $fakeApiKey = 'This is a valid api key';
        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'How are you today?'
            ]
        ];
        $httpClientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $httpClientResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_INTERNAL_SERVER_ERROR);

        $httpClientMock = $this->createMock(FakeHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        0 => 'Content-Type: application/json',
                        1 => 'Authorization: Bearer ' . $fakeApiKey
                    ],
                    'body' => json_encode([
                        'messages' => $messages,
                        'model' => 'gpt-4'
                    ]),
                ],
            )->willReturn($httpClientResponseMock);

        $chatGptService = new ChatGptService($httpClientMock, $fakeApiKey);

        $this->expectException(ApiServerErrorException::class);
        $this->expectExceptionCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage('Something wrong with the API server.');

        $chatGptService->completions($messages);
    }

    public function testMethodCompletionsReturnsServerErrorResponseWhenInvalidResponseFormatIsReturned(): void
    {
        $fakeApiKey = 'This is a valid api key';
        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'How are you today?'
            ]
        ];
        $httpClientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $httpClientResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_OK);

        $httpClientResponseMock->expects($this->once())
            ->method('getContent')
            ->with(false)
            ->willReturn('"');

        $httpClientMock = $this->createMock(FakeHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        0 => 'Content-Type: application/json',
                        1 => 'Authorization: Bearer ' . $fakeApiKey
                    ],
                    'body' => json_encode([
                        'messages' => $messages,
                        'model' => 'gpt-4'
                    ]),
                ],
            )->willReturn($httpClientResponseMock);

        $chatGptService = new ChatGptService($httpClientMock, $fakeApiKey);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage('Invalid response format.');

        $chatGptService->completions($messages);
    }

    public function testMethodCompletionsReturnsServerErrorResponseWhenUnexpectedResponseIsReturned(): void
    {
        $fakeApiKey = 'This is a valid api key';
        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'How are you today?'
            ]
        ];
        $httpClientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $httpClientResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_OK);

        $httpClientResponseMock->expects($this->once())
            ->method('getContent')
            ->with(false)
            ->willReturn(json_encode(['same' => 'same']));

        $httpClientMock = $this->createMock(FakeHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        0 => 'Content-Type: application/json',
                        1 => 'Authorization: Bearer ' . $fakeApiKey
                    ],
                    'body' => json_encode([
                        'messages' => $messages,
                        'model' => 'gpt-4'
                    ]),
                ],
            )->willReturn($httpClientResponseMock);

        $chatGptService = new ChatGptService($httpClientMock, $fakeApiKey);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage('Unexpected response from the API server.');

        $chatGptService->completions($messages);
    }

    public function testMethodCompletionsReturnsApiServerOverloadedResponseWhenApiServerIsOverloaded(): void
    {
        $fakeApiKey = 'This is a valid api key';
        $messages = [
            [
                'role' => MessageAuthorEnum::SYSTEM,
                'content' => 'You are a helpful assistant.'
            ],
            [
                'role' => MessageAuthorEnum::USER,
                'content' => 'How are you today?'
            ]
        ];
        $httpClientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $httpClientResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_SERVICE_UNAVAILABLE);

        $httpClientMock = $this->createMock(FakeHttpClient::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        0 => 'Content-Type: application/json',
                        1 => 'Authorization: Bearer ' . $fakeApiKey
                    ],
                    'body' => json_encode([
                        'messages' => $messages,
                        'model' => 'gpt-4'
                    ]),
                ],
            )->willReturn($httpClientResponseMock);

        $chatGptService = new ChatGptService($httpClientMock, $fakeApiKey);

        $this->expectException(ApiServerIsOverloadedException::class);
        $this->expectExceptionCode(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->expectExceptionMessage('API server is overloaded.');

        $chatGptService->completions($messages);
    }
}
