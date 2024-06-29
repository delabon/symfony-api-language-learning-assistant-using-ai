<?php

namespace App\Service;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ConversationService
{
    public function __construct(private readonly ConversationRepository $conversationRepository)
    {
    }

    public function get(int $id): JsonResponse|Conversation
    {
        if (!$id) {
            return new JsonResponse([
                'errors' => [
                    'conversation' => 'Invalid conversation id.'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            return new JsonResponse([
                'errors' => [
                    'conversation' => 'The conversation does not exist.'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        return $conversation;
    }
}
