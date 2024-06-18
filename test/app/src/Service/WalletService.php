<?php
/**
 * Wallet service.
 */

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
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
     * @param WalletRepository     $walletRepository  Wallet repository
     * @param PaginatorInterface $paginator      Paginator
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
    public function getPaginatedList(int $page, User $author): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->walletRepository->queryByAuthor($author),
            $page,
            WalletRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }



    public function canBeDeleted(Wallet $wallet): void
    {
        $result = $this->transactionRepository->queryByWallet($wallet);
        $query = $result->getQuery();
        $transactions = $query->getResult();
        foreach($transactions as $elem){

            $this->transactionRepository->delete($elem);
        }



    }



    public function CountWalletSum(Wallet $selectedentity, int $sum, bool $value): void
    {

        $wallet = $this->walletRepository->find($selectedentity->getId()); //szuka portfela z formu
        $walletSum = $wallet->getSum(); //pobiera wartosc portfela

        $this->walletRepository->countWalletBalance( $sum, $value, $wallet, $walletSum);



                }















}
