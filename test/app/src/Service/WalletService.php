<?php
/**
 * Wallet service.
 */

namespace App\Service;

use App\Repository\WalletRepository;
use App\Entity\Wallet;
use App\Service\WalletServiceInterface;
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
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param WalletRepository     $walletRepository  Wallet repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(WalletRepository $walletRepository, PaginatorInterface $paginator)
    {
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
        if ($wallet->getId() == null) {
            $wallet->setCreatedAt(new \DateTimeImmutable());
        }
        $wallet->setUpdatedAt(new \DateTimeImmutable());

        $this->walletRepository->save($wallet);
    }

    public function delete(Wallet $wallet): void
    {
        $this->walletRepository->delete($wallet);
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
            $this->walletRepository->queryAll(),
            $page,
            WalletRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }
}
