<?php
/**
 * Transaction service.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\TransactionRepository;
use App\Entity\Transaction;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;

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
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService       Category service
     * @param PaginatorInterface       $paginator             Paginator
     * @param TransactionRepository    $transactionRepository Transaction repository
     */
    public function __construct(CategoryServiceInterface $categoryService, PaginatorInterface $paginator, TransactionRepository $transactionRepository)
    {
        $this->categoryService = $categoryService;
        $this->paginator = $paginator;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Save entity.
     *
     * @param Transaction $transaction Category entity
     */
    public function save(Transaction $transaction): void
    {
        if (null == $transaction->getId()) {
            $transaction->setCreatedAt(new \DateTimeImmutable());
        }

        $this->transactionRepository->save($transaction);
    }

    /**
     * Delete entity.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function delete(Transaction $transaction): void
    {
        $this->transactionRepository->delete($transaction);
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */

    //     public function getPaginatedList(int $page, User $author, array $filters = []): PaginationInterface
    //     {
    //
    //
    //         $filters = $this->prepareFilters($filters);
    //
    //         return $this->paginator->paginate(
    //             $this->transactionRepository->queryByAuthor($author, $filters),
    //             $page,
    //             TransactionRepository::PAGINATOR_ITEMS_PER_PAGE
    //         );
    //     }

    /**
     * Get paginated list.
     *
     * @param int   $page    Page number
     * @param User  $author  Author
     * @param array $filters Filters
     *
     * @return array{transactions: PaginationInterface, balance: float} Paginated list with balance
     */
    public function getPaginatedList(int $page, User $author, array $filters = []): array
    {
        $filters = $this->prepareFilters($filters);

        $queryBuilder = $this->transactionRepository->queryByAuthor($author, $filters);
        $transactions = $this->paginator->paginate(
            $queryBuilder,
            $page,
            TransactionRepository::PAGINATOR_ITEMS_PER_PAGE
        );

        $transactionCollection = new ArrayCollection($transactions->getItems());

        $balance = $this->transactionRepository->calculateTotalAmount($transactionCollection);

        return ['transactions' => $transactions, 'balance' => $balance];
    }

    /**
     * Get transactions by date.
     *
     * @param int                $page      Page number
     * @param User               $user      User
     * @param \DateTimeInterface $startDate Start date
     * @param \DateTimeInterface $endDate   End date
     *
     * @return array{transactions: PaginationInterface, balance: float} Paginated list with balance
     */
    public function getByDate(int $page, User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $queryBuilder = $this->transactionRepository->findByDate($startDate, $endDate, $user);
        $transactions = $this->paginator->paginate(
            $queryBuilder,
            $page,
            TransactionRepository::PAGINATOR_ITEMS_PER_PAGE
        );

        $transactionCollection = new ArrayCollection($transactions->getItems());

        $balance = $this->transactionRepository->calculateTotalAmount($transactionCollection);

        return ['transactions' => $transactions, 'balance' => $balance];
    }

    /**
     * Prepare filters for the categories list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     */
    private function prepareFilters(array $filters): array
    {
        $resultFilters = [];
        if (!empty($filters['category_id'])) {
            $category = $this->categoryService->findOneById($filters['category_id']);
            if ($category instanceof Category) {
                $resultFilters['category'] = $category;
            }
        }

        return $resultFilters;
    }
}
