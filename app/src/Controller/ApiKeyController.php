<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\ApiKeyGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiKeyController extends AbstractController
{
    /**
     * Regenerate api key, this endpoint does not require authentication because the user could lose his api key
     * @param UserRepository $userRepository
     * @param Request $request
     * @param ApiKeyGenerator $apiKeyGenerator
     * @return JsonResponse
     */
    #[Route('/api-key/regenerate', name: 'api_key_regenerate', methods: ['PATCH'])]
    public function index(
        UserRepository $userRepository,
        Request $request,
        ApiKeyGenerator $apiKeyGenerator
    ): JsonResponse {
        $email = $request->getPayload()->get('email');

        if (!$email) {
            return $this->json([
                'success' => false,
                'error' => 'The email is required.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'The email does not exist.',
            ], Response::HTTP_NOT_FOUND);
        }

        $user->setApiKey($apiKeyGenerator->generate($user));

        return $this->json([
            'success' => true,
            'api_key' => $user->getApiKey(),
        ]);
    }
}
