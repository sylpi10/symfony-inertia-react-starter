<?php

namespace App\Repository;

use App\Entity\Techno;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\MoveDirection;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Techno>
 */
class TechnoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Techno::class);
    }

    /**
     * @return list<Techno>
     */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['position' => 'ASC']);
    }


    /**
       * @return Paginator<Techno>
       */
      public function paginateOrdered(int $page, int $perPage): Paginator
      {
          $query = $this->createQueryBuilder('t')
              ->orderBy('t.position', 'ASC')
              ->setFirstResult(($page - 1) * $perPage)
              ->setMaxResults($perPage)
              ->getQuery();

          return new Paginator($query, fetchJoinCollection: false);
      }

      public function nextPosition(): int
      {
          $max = $this->createQueryBuilder('t')
              ->select('MAX(t.position)')
              ->getQuery()
              ->getSingleScalarResult();

          return null === $max ? 0 : (int) $max + 1;
      }

      public function findNeighbor(Techno $techno, MoveDirection $direction): ?Techno
      {
          $up = MoveDirection::Up === $direction;

          return $this->createQueryBuilder('t')
              ->andWhere($up ? 't.position < :position' : 't.position > :position')
              ->setParameter('position', $techno->getPosition())
              ->orderBy('t.position', $up ? 'DESC' : 'ASC')
              ->setMaxResults(1)
              ->getQuery()
              ->getOneOrNullResult();
      }

//    /**
//     * @return Techno[] Returns an array of Techno objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Techno
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
