<?php
/**
 * Wallet repository.
 */

namespace App\Repository;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Wallet>
 *
 * @method Wallet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wallet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wallet[]    findAll()
 * @method Wallet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WalletRepository extends ServiceEntityRepository
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
    public const PAGINATOR_ITEMS_PER_PAGE = 3;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    /**
     * Add entity to the database.
     *
     * @param Wallet $entity Wallet entity
     * @param bool   $flush  Whether to flush the changes (default: false)
     */
    public function add(Wallet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove entity from the database.
     *
     * @param Wallet $entity Wallet entity
     * @param bool   $flush  Whether to flush the changes (default: false)
     */
    public function remove(Wallet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
          /*  ->select(
                'partial wallet.{id, name, createdAt, updatedAt, currency, transaction }',
                'partial transaction.{id}',
                'partial currency.{id, name}',
            )
            ->join('wallet.transaction', 'transaction')
            ->join('wallet.currency', 'currency') */
            ->orderBy('wallet.updatedAt', 'DESC');
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
        return $queryBuilder ?? $this->createQueryBuilder('wallet');
    }

    /**
     * Save entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet): void
    {
        $this->_em->persist($wallet);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function delete(Wallet $wallet): void
    {
        $this->_em->remove($wallet);
        $this->_em->flush();
    }

    /**
     * Query wallets by author.
     *
     * @param User $user User entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(User $user): QueryBuilder
    {
        $queryBuilder = $this->queryAll();

        $queryBuilder->andWhere('wallet.author = :author')
            ->setParameter('author', $user);

        return $queryBuilder;
    }

    /**
     * Count wallet balance and update it if possible.
     *
     * @param int    $sum       Amount to add or subtract
     * @param bool   $value     Whether to add (true) or subtract (false)
     * @param Wallet $wallet    Wallet entity
     * @param int    $walletSum Current wallet balance
     *
     * @return bool Whether the operation was successful
     */
    public function countWalletBalance(int $sum, bool $value, Wallet $wallet, int $walletSum): bool
    {
        if ($value) {
            if ($walletSum + $sum >= 0) {
                $wallet->setSum($walletSum + $sum);
                $this->save($wallet);

                return true;
            }
        } elseif ($walletSum - $sum >= 0) {
            $wallet->setSum($walletSum - $sum);
            $this->save($wallet);

            return true;
        }

        return false;
    }

    //    /**
    //     * @return Wallet[] Returns an array of Wallet objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Wallet
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
