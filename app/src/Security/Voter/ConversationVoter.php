<?php

namespace App\Security\Voter;

use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @phpstan-extends Voter<'delete', Conversation>
 */
class ConversationVoter extends Voter
{
    public const DELETE = 'CONVERSATION_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute == self::DELETE && $subject instanceof Conversation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        /** @var User $conversationOwner */
        $conversationOwner = $subject->getUserEntity();

        return $user->getId() === $conversationOwner->getId();
    }
}
