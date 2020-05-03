<?php

namespace Checker;

use Doctrine\ORM\EntityManager;
use Mmc\Security\Entity\UserAuthSession;

class SessionChecker
{
    protected $em;

    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }

    public function check($uuid): bool
    {
        $qb = $this->em->getRepository(UserAuthSession::class)->createQueryBuilder('s')
            ->where('s.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->update()
            ->set('s.updatedAt', ':now')
            ->setParameter('now', new \Datetime())
            ;

        $nbAffectedRows = $qb->getQuery()->execute();

        return $nbAffectedRows > 0;
    }
}
