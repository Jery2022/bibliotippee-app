<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public const COMMENTS_PER_PAGE = 4;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getCommentPaginator(Comment $comment, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->andWhere('c.comment = :comment')
            ->setParameter('comment', $comment)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(self::COMMENTS_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
        ;

        return new Paginator($query);
    }

    //    /**
    //     * @return Comment[] Returns an array of Comment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Comment
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getAverageByComment($documentId): float | null
    {
        return $this->createQueryBuilder('c')
            ->select('AVG(c.rate) as averageRate')
            ->where('c.documents = :documentId')
            ->setParameter('documentId', $documentId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCommentsByDocument($documentId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.documents = :documentId')
            ->setParameter('documentId', $documentId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    public function getComments($isValided): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.comment = :isValided')
            ->setParameter('isValided', $isValided)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    public function getCommentByDocumentByUser($documentId, $userId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.documents = ?0')
            ->andWhere('c.users = ?1')
            ->setParameter(0, $documentId)
            ->setParameter(1, $userId)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
