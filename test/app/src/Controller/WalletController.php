<?php
/**
 * Wallet controller.
 */

namespace App\Controller;

use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WalletController.
 */
#[Route('/wallet')]
class WalletController extends AbstractController
{
    /**
     * Index action.
     *
     * @param WalletRepository $walletRepository Wallet repository
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'wallet_index',
        methods: 'GET'
    )]
    public function index(WalletRepository $walletRepository): Response
    {
        $wallet = $walletRepository->findAll();

        return $this->render(
            'wallet/index.html.twig',
            ['wallet' => $wallet]
        );
    }

    /**
     * Show action.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'wallet_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Wallet $wallet): Response
    {
        return $this->render(
            'wallet/show.html.twig',
            ['wallet' => $wallet]
        );
    }
}