<?php
/**
 * Transaction service interface.
 */

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface WalletServiceInterface.
 */
interface TransactionServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int   $page    Page number
     * @param User  $author  Author of the transactions
     * @param array $filters Filters to apply
     *
     * @return array{transactions: PaginationInterface, balance: float} Paginated list with balance
     */
    public function getPaginatedList(int $page, User $author, array $filters = []): array;

    /**
     * Get paginated list by date.
     *
     * @param int                $page      Page number
     * @param User               $user      User for whom transactions are fetched
     * @param \DateTimeInterface $startDate Start date
     * @param \DateTimeInterface $endDate   End date
     *
     * @return array{transactions: PaginationInterface, balance: float} Paginated list with balance
     */
    public function getByDate(int $page, User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array;

    /**
     * Save entity.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function save(Transaction $transaction): void;

    /**
     * Delete entity.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function delete(Transaction $transaction): void;
}
