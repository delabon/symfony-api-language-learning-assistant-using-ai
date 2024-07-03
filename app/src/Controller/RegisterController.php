<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ApiKeyGenerator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function store(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ApiKeyGenerator $apiKeyGenerator
    ): JsonResponse {
        $user = User::createFromArray($request->getPayload()->all());
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new DateTimeImmutable());
        $user->setUpdatedAt($user->getCreatedAt());

        $violations = $validator->validate($user);

        if (count($violations)) {
            $inputErrors = [];

            foreach ($violations as $violation) {
                $inputErrors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return $this->json([
                'success' => false,
                'input_errors' => $inputErrors
            ], Response::HTTP_BAD_REQUEST);
        }

        $apiKey = $apiKeyGenerator->generate($user);
        $user->setApiKey($apiKey);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'api_key' => $apiKey,
        ]);
    }
}
