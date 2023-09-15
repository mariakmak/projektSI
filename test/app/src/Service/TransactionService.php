<?php
/**
 * Transaction service.
 */

namespace App\Service;

use App\Repository\TransactionRepository;
use App\Entity\Transaction;
use App\Service\TransactionServiceInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class WalletService.
 */
class TransactionService implements TransactionServiceInterface
{
    /**
     * Transaction repository.
     */
    private TransactionRepository $transactionRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param TransactionRepository     $transactionRepository  Transaction repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(TransactionRepository $transactionRepository, PaginatorInterface $paginator)
    {
        $this->transactionRepository = $transactionRepository;
        $this->paginator = $paginator;
    }



    /**
     * Save entity.
     *
     * @param Transaction $transaction Categories entity
     */
    public function save(Transaction $transaction): void
    {
        if ($transaction->getId() == null) {
            $transaction->setCreatedAt(new \DateTimeImmutable());
        }
        $transaction->setUpdatedAt(new \DateTimeImmutable());

        $this->transactionRepository->save($transaction);
    }





    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->transactionRepository->queryAll(),
            $page,
            TransactionRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }
}