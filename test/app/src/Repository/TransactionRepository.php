<?php
/**
 * Transaction repository.
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;

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
     * @param array<string, object> $filters Filters array
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(array $filters): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select(
                'partial transaction.{id, name, description, createdAt, sum, value}',
                'partial category.{id, name}',
                'partial currency.{id, name}',
                'partial wallet.{id, name}',
                'partial author.{id, email}',
            )
            ->join('transaction.category', 'category')
            ->join('transaction.wallet', 'wallet')
            ->join('wallet.currency', 'currency')
            ->leftJoin('transaction.author', 'author')
            ->orderBy('transaction.createdAt', 'DESC');

        return $this->applyFiltersToList($queryBuilder, $filters);
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

    /**
     * Add transaction entity.
     *
     * @param Transaction $entity Transaction entity to add
     * @param bool        $flush  Whether to flush EntityManager after persisting (default: false)
     */
    public function add(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove transaction entity.
     *
     * @param Transaction $entity Transaction entity to remove
     * @param bool        $flush  Whether to flush EntityManager after removing (default: false)
     */
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
     * Query transactions by author.
     *
     * @param UserInterface         $user    User entity
     * @param array<string, object> $filters Filters array
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(UserInterface $user, array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('transaction.author = :author')
            ->setParameter('author', $user);

        return $queryBuilder;
    }

    /**
     * Query transactions by wallet.
     *
     * @param Wallet                $wallet  Wallet entity
     * @param array<string, object> $filters Filters array
     *
     * @return QueryBuilder Query builder
     */
    public function queryByWallet(Wallet $wallet, array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('transaction.wallet = :wallet')
            ->setParameter('wallet', $wallet);

        return $queryBuilder;
    }

    /**
     * Query transactions count by category.
     *
     * @param Category $category The category entity
     *
     * @return int The number of transactions
     *
     * @throws \RuntimeException If an error occurs during the query
     */
    public function queryByCategory(Category $category): int
    {
        $queryBuilder = $this->getOrCreateQueryBuilder();

        try {
            return $queryBuilder->select($queryBuilder->expr()->countDistinct('transaction.id'))
                ->where('transaction.category = :category')
                ->setParameter(':category', $category)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NonUniqueResultException|NoResultException $e) {
            throw new \RuntimeException(sprintf('Error while querying by category: %s', $e->getMessage()));
        }
    }

    /**
     * Find transactions by date range and user.
     *
     * @param \DateTimeInterface $startDate Start date
     * @param \DateTimeInterface $endDate   End date
     * @param User               $user      User entity
     *
     * @return array<Transaction> Array of Transaction objects
     */
    public function findByDate(\DateTimeInterface $startDate, \DateTimeInterface $endDate, User $user): array
    {
        // var_dump($startDate);
        // var_dump($endDate);
        // var_dump($user);
        $queryBuilder = $this->createQueryBuilder('t')
            ->andWhere('t.createdAt >= :startDate')
            ->andWhere('t.createdAt <= :endDate')
            ->andWhere('t.author = :user')
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d '))
            ->setParameter('user', $user);
        // var_dump($queryBuilder);

        $query = $queryBuilder->getQuery();

        // var_dump($query);
        return $query->getResult();
    }

    /**
     * Calculate total amount from a collection of transactions.
     *
     * @param Collection<Transaction> $transactions Collection of Transaction objects
     *
     * @return float Total amount calculated
     */
    public function calculateTotalAmount(Collection $transactions): float
    {
        $totalAmount = 0;

        foreach ($transactions as $transaction) {
            $totalAmount += $transaction->getValue() ? $transaction->getSum() : -$transaction->getSum();
        }

        return $totalAmount;
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('transaction');
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
