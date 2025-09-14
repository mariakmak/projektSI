<?php

/**
 * Wallet service interface.
 */

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface WalletServiceInterface.
 */
interface WalletServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int  $page   Page number
     * @param User $author user entity
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet): void;

    /**
     * Delete entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function delete(Wallet $wallet): void;

    /**
     * Check if a wallet can be deleted.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function canBeDeleted(Wallet $wallet): bool;

    /**
     * Update wallet balance when a transaction is created.
     *
     * @param Transaction $transaction Transaction entity to apply
     *
     * @return bool True if balance was successfully updated, false otherwise
     */
    public function updateBalanceOnTransactionCreate(Transaction $transaction): bool;

    /**
     * Update wallet balance when a transaction is deleted.
     *
     * If the transaction was income (`value = true`), its sum is subtracted.
     * If it was expense (`value = false`), its sum is added back.
     *
     * @param Transaction $transaction Transaction entity to revert
     */
    public function updateBalanceOnTransactionDelete(Transaction $transaction): void;
}
