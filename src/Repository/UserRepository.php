<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function activateToken(string $token)
    {
        return $this->createQueryBuilder('u')
            ->update()
            ->set('u.confirmed', ':confirmed')
            ->setParameter('confirmed', true)
            ->set('u.token', ':null')
            ->setParameter('null', null)
            ->where('u.token = :token')
            ->setParameter('token', $token)
            ->getQuery()->execute();
    }
}
