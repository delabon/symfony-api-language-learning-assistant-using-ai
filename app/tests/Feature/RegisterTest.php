<?php

namespace App\Tests\Feature;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\FeatureTestCase;
use Doctrine\ORM\EntityManagerInterface;

class RegisterTest extends FeatureTestCase
{
    private ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }

    public function testRegistersUsersSuccessfully(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        // Count the number of users before registration
        $usersBeforeRegistration = count($userRepository->findAll());

        $this->client->request(
            'POST',
            '/register',
            parameters: [
                'name' => 'John Doe',
                'username' => 'johndoe',
                'email' => 'johndoe@example.com',
            ]
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('api_key', $responseData['data']);
        $this->assertIsString($responseData['data']['api_key']);
        $this->assertEquals(128, strlen($responseData['data']['api_key']));

        // Count the number of users after registration
        $users = $userRepository->findAll();
        $usersAfterRegistration = count($users);

        // Check if a new user has been registered
        $this->assertEquals($usersBeforeRegistration + 1, $usersAfterRegistration);
        $this->assertSame('John Doe', $users[0]->getName());
        $this->assertSame('johndoe@example.com', $users[0]->getEmail());
        $this->assertSame('johndoe', $users[0]->getUsername());
        $this->assertEquals(128, strlen($users[0]->getApiKey()));
    }

    // username must be unique
    // email must be unique
    // api key must be unique
    // validate data
}
