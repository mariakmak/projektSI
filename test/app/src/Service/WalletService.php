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
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
     *
     * @return bool True if wallet can be deleted, false otherwise
     */
    public function canBeDeleted(Wallet $wallet): bool
    {
        try {
            $result = $this->transactionRepository->queryByWallet($wallet)
                ->getQuery()
                ->getResult();

            return empty($result);
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }

    /**
     * Update wallet balance when a transaction is created.
     *
     * @param Transaction $transaction Transaction entity to apply
     *
     * @return bool True if balance was successfully updated, false otherwise
     */
    public function updateBalanceOnTransactionCreate(Transaction $transaction): bool
    {
        $wallet = $transaction->getWallet();
        if (!$wallet) {
            return false;
        }

        $walletSum = $wallet->getSum();
        $sum = $transaction->getSum();
        $value = $transaction->isValue();

        if ($value) {
            $wallet->setSum($walletSum + $sum);
        } elseif ($walletSum - $sum >= 0) {
            $wallet->setSum($walletSum - $sum);
        } else {
            return false;
        }

        $this->walletRepository->save($wallet);

        return true;
    }

    /**
     * Update wallet balance when a transaction is deleted.
     *
     * If the transaction was income (`value = true`), its sum is subtracted.
     * If it was expense (`value = false`), its sum is added back.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function updateBalanceOnTransactionDelete(Transaction $transaction): void
    {
        $wallet = $transaction->getWallet();
        if (!$wallet) {
            return;
        }

        $walletSum = $wallet->getSum();
        $sum = $transaction->getSum();
        $value = $transaction->getValue();

        if ($value) {
            $wallet->setSum($walletSum - $sum);
        } else {
            $wallet->setSum($walletSum + $sum);
        }

        $this->walletRepository->save($wallet);
    }
}
