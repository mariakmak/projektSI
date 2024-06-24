<?php
/**
 * Wallet service interface.
 */

namespace App\Service;

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
     * @param User $author User entity
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
    public function canBeDeleted(Wallet $wallet): void;

    /**
     * Count wallet balance.
     *
     * @param Wallet $select Wallet entity
     * @param int    $sum    Sum to add or subtract
     * @param bool   $value  Whether to add or subtract the sum
     *
     * @return bool True if the wallet balance was updated successfully, false otherwise
     */
    public function countWalletSum(Wallet $select, int $sum, bool $value): bool;
}
