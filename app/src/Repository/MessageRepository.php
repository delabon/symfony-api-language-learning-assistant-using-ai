<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function reset(Conversation $conversation): void
    {
        $this->createQueryBuilder('m')
            ->delete()
            ->andWhere('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->getQuery()
            ->execute();
    }
}
