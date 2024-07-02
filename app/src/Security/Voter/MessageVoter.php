<?php

namespace App\Security\Voter;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @phpstan-extends Voter<'RESET', Conversation>
 */
class MessageVoter extends Voter
{
    public const RESET = 'MESSAGE_RESET';

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::RESET && ($subject instanceof Message || $subject instanceof Conversation);
    }

    /**
     * @param string $attribute
     * @param Message|Conversation $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::RESET && $subject instanceof Conversation) {
            /** @var User $conversationUser */
            $conversationUser = $subject->getUserEntity();

            return $conversationUser->getId() === $user->getId();
        }

        return false;
    }
}
