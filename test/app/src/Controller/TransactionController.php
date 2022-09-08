<?php
/**
 * Transaction controller.
 */

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TransactionController.
 */
#[Route('/transaction')]
class TransactionController extends AbstractController
{
    /**
     * Index action.
     *
     * @param TransactionRepository $transactionRepository Transaction repository
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'transaction_index',
        methods: 'GET'
    )]
    public function index(TransactionRepository $transactionRepository): Response
    {
        $transaction = $transactionRepository->findAll();

        return $this->render(
            'transaction/index.html.twig',
            ['transaction' => $transaction]
        );
    }

    /**
     * Show action.
     *
     * @param Transaction $transaction Transaction entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'transaction_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Transaction $transaction): Response
    {
        return $this->render(
            'transaction/show.html.twig',
            ['transaction' => $transaction]
        );
    }
}