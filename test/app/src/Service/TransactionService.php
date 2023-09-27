<?php
/**
 * Transaction service.
 */

namespace App\Service;

use App\Entity\Categories;
use App\Entity\User;
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
     * Category service.
     */
    private CategoriesServiceInterface $categoriesService;



    /**
     * Constructor.
     *
     * @param CategoriesServiceInterface $categoriesService Category service
     * @param PaginatorInterface       $paginator       Paginator
     * @param TransactionRepository       $transactionRepository  Transaction repository
     */
    public function __construct(
        CategoriesServiceInterface $categoriesService,
        PaginatorInterface $paginator,
        TransactionRepository $transactionRepository
    ) {
        $this->categoriesService = $categoriesService;
        $this->paginator = $paginator;
        $this->transactionRepository = $transactionRepository;
    }












    /**
     * Constructor.
     *
     * @param TransactionRepository     $transactionRepository  Transaction repository
     * @param PaginatorInterface $paginator      Paginator
     */
    //public function __construct(TransactionRepository $transactionRepository, PaginatorInterface $paginator)
    //{
     //   $this->transactionRepository = $transactionRepository;
     //   $this->paginator = $paginator;
   // }



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
    public function getPaginatedList(int $page, User $author, array $filters = []): PaginationInterface
    {


        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->transactionRepository->queryByAuthor($author, $filters),
            $page,
            TransactionRepository::PAGINATOR_ITEMS_PER_PAGE
        );
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
        if (!empty($filters['categories_id'])) {
            $categories = $this->categoriesService->findOneById($filters['categories_id']);
            if (null !== $categories) {
                $resultFilters['categories'] = $categories;
            }
        }

        return $resultFilters;
    }












}