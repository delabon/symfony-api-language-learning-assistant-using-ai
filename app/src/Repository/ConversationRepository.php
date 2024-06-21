<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Enum\LanguageEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * @param LanguageEnum $languageEnum
     * @param UserInterface $user
     * @return Conversation[]
     */
    public function findByLanguageAndUser(LanguageEnum $languageEnum, UserInterface $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.language = :language')
            ->setParameter('language', $languageEnum->value)
            ->andWhere('c.userEntity = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
