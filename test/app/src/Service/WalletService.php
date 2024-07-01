<?php
/**
 * Wallet service.
 */

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;
use App\Entity\Wallet;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class WalletService.
 */
class WalletService implements WalletServiceInterface
{
    /**
     * Wallet repository.
     */
    private WalletRepository $walletRepository;

    /**
     * transaction repository.
     */
    private TransactionRepository $transactionRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param WalletRepository      $walletRepository      Wallet repository
     * @param PaginatorInterface    $paginator             Paginator
     * @param TransactionRepository $transactionRepository Transaction repository
     */
    public function __construct(WalletRepository $walletRepository, PaginatorInterface $paginator, TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->walletRepository = $walletRepository;
        $this->paginator = $paginator;
    }

    /**
     * Save entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet): void
    {
        if (null === $wallet->getId()) {
            $wallet->setCreatedAt(new \DateTimeImmutable());
        }
        $wallet->setUpdatedAt(new \DateTimeImmutable());

        $this->walletRepository->save($wallet);
    }

    /**
     * Delete entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function delete(Wallet $wallet): void
    {
        $this->walletRepository->delete($wallet);
    }

    /**
     * Get paginated list.
     *
     * @param int  $page   Page number
     * @param User $author Author of the wallets
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->walletRepository->queryByAuthor($author),
            $page,
            WalletRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Check if wallet can be deleted by deleting all associated transactions.
     *
     * @param Wallet $wallet Wallet entity to check
     */
    public function canBeDeleted(Wallet $wallet): void
    {
        $result = $this->transactionRepository->queryByWallet($wallet);
        $query = $result->getQuery();
        $transactions = $query->getResult();
        foreach ($transactions as $elem) {
            $this->transactionRepository->delete($elem);
        }
    }

    /**
     * Count wallet sum based on provided parameters.
     *
     * @param Wallet $select Selected wallet entity
     * @param int    $sum    Sum to calculate
     * @param bool   $value  Value to calculate
     *
     * @return bool True if the wallet balance was updated successfully, false otherwise
     */
    public function countWalletSum(Wallet $select, int $sum, bool $value): bool
    {
        $wallet = $this->walletRepository->find($select->getId()); // szuka portfela z formu
        $walletSum = $wallet->getSum(); // pobiera wartosc portfela
        // dd($wallet, $walletSum);

        return $this->walletRepository->countWalletBalance($sum, $value, $wallet, $walletSum);
    }
}
