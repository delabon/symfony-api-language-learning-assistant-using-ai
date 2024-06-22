<?php

namespace App\Controller\Api\v1;

use App\Entity\Conversation;
use App\Enum\LanguageEnum;
use App\Repository\ConversationRepository;
use App\Security\Voter\ConversationVoter;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/conversation', name: 'conversation_')]
#[IsGranted('ROLE_USER')]
class ConversationController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        ConversationRepository $conversationRepository
    ): JsonResponse {
        $language = LanguageEnum::find($request->getPayload()->get('language'));

        if (!$language) {
            return $this->json([
                'success' => false,
                'error' => 'Invalid or unsupported language.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $conversations = $conversationRepository->findByLanguageAndUser($language, $security->getUser());

        if ($conversations) {
            return $this->json([
                'success' => false,
                'error' => 'A conversation with ' . $language->value . ' the language already exists.',
            ], Response::HTTP_FORBIDDEN);
        }

        $conversation = new Conversation();
        $conversation->setLanguage($language);
        $conversation->setUserEntity($security->getUser());
        $conversation->setCreatedAt(new DateTimeImmutable());
        $conversation->setUpdatedAt(new DateTimeImmutable());

        $entityManager->persist($conversation);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $conversation->getId(),
        ]);
    }

    #[Route('/delete/{id<\d+>}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(ConversationVoter::DELETE, subject: 'conversation')]
    public function delete(
        Conversation $conversation,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $entityManager->remove($conversation);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}
