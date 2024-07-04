<?php

namespace App\Controller\Api\v1;

use App\Doctrine\MessageAuthorEnum;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Exception\ApiServerErrorException;
use App\Exception\ApiServerIsOverloadedException;
use App\Exception\RateLimitException;
use App\Exception\UnsupportedRegionException;
use App\Repository\MessageRepository;
use App\Security\Voter\MessageVoter;
use App\Service\ChatGptService;
use App\Service\ChatService;
use App\Service\ConversationService;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route('/api/v1/message', name: 'message_')]
#[IsGranted('ROLE_USER')]
class MessageController extends AbstractController
{
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        ChatService $chatService,
        ValidatorInterface $validator,
        ChatGptService $chatGptService,
        ConversationService $conversationService
    ): JsonResponse {
        $body = $request->getPayload()->get('body', '');
        $conversation = $conversationService->get($request->getPayload()->getInt('conversation_id'));

        if ($conversation instanceof JsonResponse) {
            return $conversation;
        }

        $message = new Message();
        $message->setBody($body);
        $message->setConversation($conversation);
        $message->setAuthor(MessageAuthorEnum::USER);
        $message->setCreatedAt(new DateTimeImmutable());
        $message->setUpdatedAt(new DateTimeImmutable());

        $violations = $validator->validate($message);

        if (count($violations) > 0) {
            $errors = [];

            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        if (!$chatService->hasSystemMessage($conversation)) {
            $chatService->addSystemMessage($conversation);
        }

        $chatService->addUserMessage($conversation, $body);

        try {
            $aiReply = $chatGptService->completions($chatService->getMessages($conversation));
            $assistantMessage = $chatService->addAssistantMessage($conversation, $aiReply);

            return $this->json($assistantMessage, context: [
                'groups' => ['messages.list']
            ]);
        } catch (RateLimitException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_TOO_MANY_REQUESTS);
        } catch (UnsupportedRegionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (UnauthorizedHttpException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (ClientExceptionInterface $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (RedirectionExceptionInterface $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_MULTIPLE_CHOICES);
        } catch (ApiServerIsOverloadedException|TransportExceptionInterface $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (ApiServerErrorException|ServerExceptionInterface|Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/reset/{id<\d+>}', name: 'reset', methods: ['POST'])]
    #[IsGranted(MessageVoter::RESET, subject: 'conversation')]
    public function reset(
        Conversation $conversation,
        MessageRepository $messageRepository,
    ): JsonResponse {
        $messageRepository->reset($conversation);

        return $this->json(true);
    }
}
