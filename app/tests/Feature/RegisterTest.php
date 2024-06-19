<?php

namespace App\Tests\Feature;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Tests\FeatureTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Response;

class RegisterTest extends FeatureTestCase
{
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

    public function testRegistersMoreThanOneUserSuccessfully(): void
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

        $this->client->request(
            'POST',
            '/register',
            parameters: [
                'name' => 'Sami Khan',
                'username' => 'samikhan',
                'email' => 'samikhan@example.com',
            ]
        );

        // Count the number of users after registration
        $users = $userRepository->findAll();
        $usersAfterRegistration = count($users);

        // Check if a new user has been registered
        $this->assertEquals($usersBeforeRegistration + 2, $usersAfterRegistration);
        $this->assertGreaterThan(0, $users[0]->getId());
        $this->assertGreaterThan(0, $users[1]->getId());
        $this->assertNotEquals($users[0]->getId(), $users[1]->getId());
    }

    /**
     * @dataProvider userInvalidDataProvider
     * @param array<string, mixed> $data
     * @param string $input
     * @param string $message
     * @return void
     */
    public function testReturnsBadRequestWhenInvalidData(array $data, string $input, string $message): void
    {
        $this->client->request(
            'POST',
            '/register',
            parameters: $data
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('input_errors', $responseData);
        $this->assertArrayHasKey($input, $responseData['input_errors']);
        $this->assertSame($message, $responseData['input_errors'][$input]);
    }

    public function testReturnsBadRequestWhenRegisteringWithAlreadyExistentUsername(): void
    {
        $username = 'my1234';
        $userFactory = new UserFactory(Factory::create(), $this->entityManager);
        $userFactory->create([
            'username' => $username,
            'name' => 'Ahmed Kali',
            'email' => 'test@example.com'
        ]);

        $this->client->request(
            'POST',
            '/register',
            parameters: [
                'name' => 'John Doe',
                'username' => $username,
                'email' => 'johndoe@example.com',
            ]
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('input_errors', $responseData);
        $this->assertArrayHasKey('username', $responseData['input_errors']);
        $this->assertSame('This username is already used.', $responseData['input_errors']['username']);
    }

    public function testReturnsBadRequestWhenRegisteringWithAlreadyExistentEmail(): void
    {
        $email = 'test@example.com';
        $userFactory = new UserFactory(Factory::create(), $this->entityManager);
        $userFactory->create([
            'username' => $email,
            'name' => 'Ahmed Kali',
            'email' => 'test@example.com'
        ]);

        $this->client->request(
            'POST',
            '/register',
            parameters: [
                'name' => 'John Doe',
                'username' => 'johndoe',
                'email' => $email,
            ]
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('input_errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['input_errors']);
        $this->assertSame('This email is already used.', $responseData['input_errors']['email']);
    }

    /**
     * @return array<string, mixed>
     */
    public static function userInvalidDataProvider(): array
    {
        return [
            'Without username' => [
                'data' => [
                    'name' => 'John Doe',
                    'email' => 'johndoe@example.com',
                ],
                'input' => 'username',
                'message' => 'The username should be between 3 and 50 characters long.'
            ],
            'Empty username' => [
                'data' => [
                    'username' => '',
                    'name' => 'John Doe',
                    'email' => 'johndoe@example.com',
                ],
                'input' => 'username',
                'message' => 'The username should be between 3 and 50 characters long.'
            ],
            'Less than 3 chars username' => [
                'data' => [
                    'username' => 'ab',
                    'name' => 'John Doe',
                    'email' => 'johndoe@example.com',
                ],
                'input' => 'username',
                'message' => 'The username should be between 3 and 50 characters long.'
            ],
            'More than 50 chars username' => [
                'data' => [
                    'username' => str_repeat('a', 51),
                    'name' => 'John Doe',
                    'email' => 'johndoe@example.com',
                ],
                'input' => 'username',
                'message' => 'The username should be between 3 and 50 characters long.'
            ],
            'Invalid username' => [
                'data' => [
                    'username' => 'a9 - %$: sller',
                    'name' => 'John Doe',
                    'email' => 'johndoe@example.com',
                ],
                'input' => 'username',
                'message' => 'The username should only contain lowercase letters, numbers, and underscores.'
            ],
            'Without email' => [
                'data' => [
                    'username' => 'johndoe',
                    'name' => 'John Doe',
                ],
                'input' => 'email',
                'message' => 'The email should not be blank.'
            ],
            'Empty email' => [
                'data' => [
                    'username' => 'johndoe',
                    'name' => 'John Doe',
                    'email' => '',
                ],
                'input' => 'email',
                'message' => 'The email should not be blank.'
            ],
            'Invalid email' => [
                'data' => [
                    'username' => 'johndoe',
                    'name' => 'John Doe',
                    'email' => 'my emal 23#% is inalvaid',
                ],
                'input' => 'email',
                'message' => 'This value is not a valid email address.'
            ],
            'Without name' => [
                'data' => [
                    'username' => 'johndoe',
                    'email' => 'johndoe@example.com',
                ],
                'input' => 'name',
                'message' => 'The name should be between 3 and 50 characters long.'
            ],
            'Empty name' => [
                'data' => [
                    'username' => 'johndoe',
                    'name' => '',
                    'email' => 'johndoe@example.com',
                ],
                'input' => 'name',
                'message' => 'The name should be between 3 and 50 characters long.'
            ],
            'Less than 3 chars name' => [
                'data' => [
                    'username' => 'johndoe',
                    'name' => 'a',
                    'email' => 'johndoe@example.com',
                ],
                'input' => 'name',
                'message' => 'The name should be between 3 and 50 characters long.'
            ],
            'More than 50 chars name' => [
                'data' => [
                    'username' => 'johndoe',
                    'name' => str_repeat('a', 51),
                    'email' => 'johndoe@example.com',
                ],
                'input' => 'name',
                'message' => 'The name should be between 3 and 50 characters long.'
            ],
            'Invalid name' => [
                'data' => [
                    'username' => 'johndoe',
                    'name' => 'my 5# n3me .',
                    'email' => 'johndoe@example.com',
                ],
                'input' => 'name',
                'message' => 'The name should only contain letters and spaces.'
            ],
        ];
    }
}
