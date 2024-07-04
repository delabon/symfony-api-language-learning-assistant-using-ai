<?php

namespace App\Tests\Feature;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Tests\FeatureTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Response;

class RegenerateApiKeyTest extends FeatureTestCase
{
    protected const ENDPOINT = '/api-key/regenerate';

    public function testRegenerateApiKeySuccessfully(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $email = 'test@example.com';

        /** @var User $user */
        $user = (new UserFactory(Factory::create(), $this->entityManager))->create([
            'email' => $email
        ]);
        $oldApiKey = $user->getApiKey();

        $this->client->request(
            'PATCH',
            self::ENDPOINT,
            [
                'email' => $email
            ],
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);
        $fetchedUser = $userRepository->find($user->getId());

        $this->assertResponseIsSuccessful();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('api_key', $result);
        $this->assertEquals(128, strlen($result['api_key']));

        $this->assertNotSame($oldApiKey, $result['api_key']);
        $this->assertNotSame($oldApiKey, $fetchedUser->getApiKey());
        $this->assertSame($result['api_key'], $fetchedUser->getApiKey());
    }

    public function testReturnsBadRequestResponseWhenNoEmail(): void
    {
        $this->client->request(
            'PATCH',
            self::ENDPOINT,
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('The email is required.', $result['error']);
    }

    public function testReturnsNotFoundResponseWhenEmailDoesNotExistInDatabase(): void
    {
        $this->client->request(
            'PATCH',
            self::ENDPOINT,
            [
                'email' => 'nonexistent@example.com'
            ]
        );

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('The email does not exist.', $result['error']);
    }
}
