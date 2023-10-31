<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 *
 * @method Author|null find($id, $lockMode = null, $lockVersion = null)
 * @method Author|null findOneBy(array $criteria, array $orderBy = null)
 * @method Author[]    findAll()
 * @method Author[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Author $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Author $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function listAuthorByEmail()
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.email', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAuthorsByBookCountRange($minBookCount, $maxBookCount)
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->leftJoin('a.books', 'b')
            ->groupBy('a.id')
            ->having('COUNT(b) >= :minBookCount')
            ->andHaving('COUNT(b) <= :maxBookCount')
            ->setParameter('minBookCount', $minBookCount)
            ->setParameter('maxBookCount', $maxBookCount)
            ->getQuery()
            ->getResult();
    }

    // Ajoutez cette méthode pour supprimer les auteurs avec nb_books = 0
    public function deleteAuthorsWithZeroBooks()
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->delete()
            ->where($qb->expr()->eq('a.nb_books', 0))
            ->getQuery()
            ->execute();
    }
    // /**
    //  * @return Author[] Returns an array of Author objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Author
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
