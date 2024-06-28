<?php

namespace App\Service;

use App\Exception\ApiServerErrorException;
use App\Exception\ApiServerIsOverloadedException;
use App\Exception\RateLimitException;
use App\Exception\UnsupportedRegionException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

class ChatGptService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        #[Autowire('%openai_secret%')]
        private readonly string $apiKey
    ) {
    }

    /**
     * @param array<int, array<string, string>> $messages
     * @return string
     * @throws ApiServerErrorException
     * @throws ApiServerIsOverloadedException
     * @throws ClientExceptionInterface
     * @throws RateLimitException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedRegionException
     */
    public function completions(array $messages): string
    {
        $result = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                0 => 'Content-Type: application/json',
                1 => 'Authorization: Bearer ' . $this->apiKey
            ],
            'body' => json_encode([
                'messages' => $messages,
                'model' => 'gpt-4'
            ]),
        ]);

        $result = match ($result->getStatusCode()) {
            Response::HTTP_UNAUTHORIZED => throw new UnauthorizedHttpException($this->apiKey, 'Invalid Authentication.', code: Response::HTTP_UNAUTHORIZED),
            Response::HTTP_FORBIDDEN => throw new UnsupportedRegionException('Country, region, or territory not supported.', Response::HTTP_FORBIDDEN),
            Response::HTTP_TOO_MANY_REQUESTS => throw new RateLimitException('Rate limit reached for requests.', Response::HTTP_TOO_MANY_REQUESTS),
            Response::HTTP_INTERNAL_SERVER_ERROR => throw new ApiServerErrorException('Something wrong with the API server.', Response::HTTP_INTERNAL_SERVER_ERROR),
            Response::HTTP_SERVICE_UNAVAILABLE => throw new ApiServerIsOverloadedException('API server is overloaded.', Response::HTTP_SERVICE_UNAVAILABLE),
            default => json_decode($result->getContent(false), true),
        };

        if (!$result) {
            throw new UnexpectedValueException('Invalid response format.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (!isset($result['choices'], $result['choices'][0], $result['choices'][0]['message'], $result['choices'][0]['message']['content'])) {
            throw new UnexpectedValueException('Unexpected response from the API server.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $result['choices'][0]['message']['content'];
    }
}
