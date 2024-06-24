<?php
/**
 * Transaction controller.
 */

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Form\Type\TransactionType;
use App\Service\TransactionServiceInterface;
use App\Service\WalletService;
use Form\Type\FilterType;
use JetBrains\PhpStorm\ArrayShape;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TransactionController.
 */
#[\Symfony\Component\Routing\Attribute\Route('/transaction')]
class TransactionController extends AbstractController
{
    /**
     * Transaction service.
     */
    private TransactionServiceInterface $transactionService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Wallet repository.
     */
    private WalletService $walletService;

    /**
     * Constructor.
     *
     * @param TransactionServiceInterface $transactionService Transaction service
     * @param WalletService               $walletService      Wallet service
     * @param TranslatorInterface         $translator         Translator
     */
    public function __construct(TransactionServiceInterface $transactionService, WalletService $walletService, TranslatorInterface $translator)
    {
        $this->transactionService = $transactionService;
        $this->walletService = $walletService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[\Symfony\Component\Routing\Attribute\Route(
        name: 'transaction_index',
        methods: ['GET']
    )]
    public function index(Request $request): Response
    {
        // ob_start();

        $user = $this->getUser();
        $transactions = [];
        $balance = null;

        $form = $this->createForm(FilterType::class, null, [
            'method' => 'GET',
        ]);


        $form->handleRequest($request);
        // var_dump('Form:', $form);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            // var_dump('Form Data:', $formData);

            $startDateString = $formData['start_date'] ? $formData['start_date']->format('Y-m-d') : null;
            $endDateString = $formData['end_date'] ? $formData['end_date']->format('Y-m-d') : null;

            // var_dump('Start Date String:', $startDateString);
            // var_dump('End Date String:', $endDateString);

            if ($startDateString && $endDateString) {
                // $startDate = \DateTime::createFromFormat('Y-m-d', $startDateString . ' 00:00:00');
                // $endDate = \DateTime::createFromFormat('Y-m-d', $endDateString . ' 23:59:59');
                $startDate = \DateTime::createFromFormat('Y-m-d', $startDateString);
                $endDate = \DateTime::createFromFormat('Y-m-d', $endDateString);

                // var_dump('Start Date:', $startDate);
                // var_dump('End Date:', $endDate);

                if (false === $startDate || false === $endDate) {
                    $this->addFlash(
                        'error',
                        $this->translator->trans('message.data_error')
                    );
                } else {
                    $paginationData = $this->transactionService->getByDate(
                        $request->query->getInt('page', 1),
                        $user,
                        $startDate,
                        $endDate
                    );

                    $transactions = $paginationData['transactions'];
                    $balance = $paginationData['balance'];
                    // var_dump('Filtered Transactions:', $transactions);
                }
            } else {
                $this->addFlash(
                    'error',
                    $this->translator->trans('message.data_missing')
                );
            }
        } else {
            $filters = $this->getFilters($request);
            // var_dump('Filters:', $filters);

            $paginationData = $this->transactionService->getPaginatedList(
                $request->query->getInt('page', 1),
                $user,
                $filters
            );

            $transactions = $paginationData['transactions'];
            $balance = $paginationData['balance'];
            // var_dump('All Transactions:', $transactions);
        }

        // ob_end_flush();

        return $this->render(
            'transaction/index.html.twig',
            [
                'pagination' => $transactions,
                'balance' => $balance,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     */
    #[ArrayShape(['category_id' => "int", 'created_at' => "int"])]
    private function getFilters(Request $request): array
    {
        return ['category_id' => $request->query->getInt('filters_category_id'), 'created_at' => $request->query->getInt('filters_created_at')];
    }

    /**
     * Show action.
     *
     * @param Transaction $transaction Transaction entity
     *
     * @return Response HTTP response
     */
    #[\Symfony\Component\Routing\Attribute\Route(
        '/{id}',
        name: 'transaction_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    #[IsGranted('VIEW', subject: 'transaction')]
    public function show(Transaction $transaction): Response
    {
        return $this->render(
            'transaction/show.html.twig',
            ['transaction' => $transaction]
        );
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[\Symfony\Component\Routing\Attribute\Route(
        '/create',
        name: 'transaction_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $transaction = new Transaction();
        $transaction->setAuthor($user);

        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        // dd($request, $form);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($request);
            $data = $form->getData(); // dane z formu
            $selectedentity = $data->getwallet();

            $sum = $data->getSum(); // sum z form
            $value = $data->isValue(); // value z form

            $a = $this->walletService->countWalletSum($selectedentity, $sum, $value);
            if ($a === false) {
                $this->addFlash(
                    'notice',
                    $this->translator->trans('message.value')
                );
            }
            $this->transactionService->save($transaction);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render(
            'transaction/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Delete action.
     *
     * @param Request     $request     HTTP request
     * @param Transaction $transaction Transaction entity
     *
     * @return Response HTTP response
     */
    #[
        \Symfony\Component\Routing\Attribute\Route('/{id}/delete', name: 'transaction_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('DELETE', subject: 'transaction')]
    public function delete(Request $request, Transaction $transaction): Response
    {
        $form = $this->createForm(FormType::class, $transaction, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('transaction_delete', ['id' => $transaction->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->transactionService->delete($transaction);
            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render(
            'transaction/delete.html.twig',
            [
                'form' => $form->createView(),
                'category' => $transaction,
            ]
        );
    }
}
