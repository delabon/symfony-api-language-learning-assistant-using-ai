<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function store(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $user = new User();
        $user->setName($request->getPayload()->get('name'));
        $user->setUsername($request->getPayload()->get('username'));
        $user->setEmail($request->getPayload()->get('email'));
        $user->setApiKey(hash('sha512', uniqid() . $user->getEmail() . $user->getUsername() . $this->getParameter('app_secret')));
        $user->setCreatedAt(new DateTimeImmutable());
        $user->setUpdatedAt($user->getCreatedAt());

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'data' => [
                'api_key' => $user->getApiKey(),
            ],
        ]);
    }
}
