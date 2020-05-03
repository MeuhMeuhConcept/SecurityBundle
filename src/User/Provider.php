<?php

namespace Mmc\Security\User;

use Doctrine\ORM\EntityManagerInterface;
use Mmc\Security\Entity\UserAuth;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class Provider implements UserProviderInterface
{
    protected $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me.
     *
     * If you're not using these features, you do not need to implement
     * this method.
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $qb = $this->em->getRepository(UserAuth::class)->createQueryBuilder('a')
            ->innerJoin('a.user', 'u')
            ->andWhere('a.uuid = :uuid')
            ->setParameter('uuid', $username)
            ->andWhere('a.isEnabled = true')
            ->andWhere('u.isEnabled = true')
            ;

        $entity = $qb->getQuery()->getOneOrNullResult();

        return $this->buildUser($entity);
    }

    public function loadUserByTypeAndKey($type, $key)
    {
        $qb = $this->em->getRepository(UserAuth::class)->createQueryBuilder('a')
            ->innerJoin('a.user', 'u')
            ->andWhere('a.type = :type')
            ->setParameter('type', $type)
            ->andWhere('a.key = :key')
            ->setParameter('key', $key)
            ->andWhere('a.isEnabled = true')
            ->andWhere('u.isEnabled = true')
            ;

        $entity = $qb->getQuery()->getOneOrNullResult();

        return $this->buildUser($entity);
    }

    protected function buildUser(UserAuth $entity)
    {
        if (!$entity) {
            throw new UsernameNotFoundException();
        }

        return new User(
            $entity->getUuid(),
            $entity->getUser()->getUuid(),
            $entity->getType(),
            $entity->getKey(),
            $entity->getIsVerified(),
            $entity->getDatas()
        );
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Return a User object after making sure its data is "fresh".
        // Or throw a UsernameNotFoundException if the user no longer exists.
        throw new \Exception('TODO: fill in refreshUser() inside '.__FILE__);
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
