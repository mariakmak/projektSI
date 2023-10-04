<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{

    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in configuration files.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 5;




    /**
     * Query all records.
     *
     * @return \Doctrine\ORM\QueryBuilder Query builder
     */
    public function queryAll(array $filters): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select(
                'partial transaction.{id, description, createdAt, sum, value}',
                'partial categories.{id, name}',
                'partial currency.{id, name}',
                'partial wallet.{id, name}',
            )
            ->join('transaction.category', 'categories')
            ->join('transaction.currency', 'currency')
            ->join('transaction.wallet', 'wallet')
            ->orderBy('transaction.createdAt', 'DESC');

        return $this->applyFiltersToList($queryBuilder, $filters);
    }






    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('transaction');
    }



    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function add(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Save entity.
     *
     * @param Transaction $transaction Transaction entity
     */

    public function save(Transaction $transaction): void
    {
        $this->_em->persist($transaction);
        $this->_em->flush();
    }


    /**
     * Delete entity.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function delete(Transaction $transaction): void
    {
        $this->_em->remove($transaction);
        $this->_em->flush();
    }

    /**
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(UserInterface $user, array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('transaction.author = :author')
            ->setParameter('author', $user);

        return $queryBuilder;
    }



    public function queryByWallet(Wallet $wallet, array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('transaction.wallet = :wallet')
            ->setParameter('wallet', $wallet);

        return $queryBuilder;
    }






    /**
     * Apply filters to paginated list.
     *
     * @param QueryBuilder          $queryBuilder Query builder
     * @param array<string, object> $filters      Filters array
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, array $filters = []): QueryBuilder
    {
        if (isset($filters['category']) && $filters['category'] instanceof Category) {
            $queryBuilder->andWhere('category = :category')
                ->setParameter('category', $filters['category']);
        }


        return $queryBuilder;
    }


    public function addtowallet():void{

    }


    public function queryByCategory(Category $category): int
    {
        $queryBuilder = $this->getOrCreateQueryBuilder();

        return $queryBuilder->select($queryBuilder->expr()->countDistinct('transaction.id'))
            ->where('transaction.category = :category')
            ->setParameter(':category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }


//    /**
//     * @return Transaction[] Returns an array of Transaction objects
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

//    public function findOneBySomeField($value): ?Transaction
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
