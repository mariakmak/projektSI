<?php
/**
 * Transaction controller.
 */

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\Form\Type\TransactionType;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Repository\WalletRepository;
use App\Service\TransactionServiceInterface;
use App\Service\WalletService;
use Form\Type\FilterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Contracts\Translation\TranslatorInterface;
use DateTime;

/**
 * Class TransactionController.
 */
#[Route('/transaction')]
class TransactionController extends AbstractController
{


    /**
     * Transaction service.
     */
    private TransactionServiceInterface $transactionService;


    /**
     * Wallet repository.
     */
    private WalletService $walletService;


    /**
     * Constructor.
     */
    public function __construct(TransactionServiceInterface $transactionService, WalletService $walletService)
    {
        $this->transactionService = $transactionService;
        $this->walletService = $walletService;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     * @return Response HTTP response
     */
    #[Route(
        name: 'transaction_index',
        methods: ['GET']
    )]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $transactions = [];
        $balance = null;


        $formData = $request->query->get('filter', []);
        $startDateString = $formData['start_date'] ?? null;
        $endDateString = $formData['end_date'] ?? null;

        #var_dump($startDateString, $endDateString);

        if ($startDateString && $endDateString) {
            #var_dump($startDateString);
            #var_dump($endDateString);
            $startDate = \DateTime::createFromFormat('Y-m-d', $formData['start_date']['year'] . '-' . $formData['start_date']['month'] . '-' . $formData['start_date']['day']);
            $endDate = \DateTime::createFromFormat('Y-m-d', $formData['end_date']['year'] . '-' . $formData['end_date']['month'] . '-' . $formData['end_date']['day']);

            #var_dump($startDate);
            #var_dump($endDate);



            if ($startDate === false || $endDate === false) {
                // Obsługa błędnego formatu daty
                $this->addFlash(
                    'error',
                    $this->translator->trans('message.data_error')
                );
            }

            $paginationData = $this->transactionService->getByDate(
                $request->query->getInt('page', 1),
                $user,
                $startDate,
                $endDate
            );

            $transactions = $paginationData['transactions'];
            $balance = $paginationData['balance'];
        } else {
            $filters = $this->getFilters($request);
            $paginationData = $this->transactionService->getPaginatedList(
                $request->query->getInt('page', 1),
                $user,
                $filters
            );

            $transactions = $paginationData['transactions'];
            $balance = $paginationData['balance'];
        }

        $form = $this->createForm(FilterType::class);
        $form->submit($formData);

        return $this->render(
            'transaction/index.html.twig',
            [
                'pagination' => $transactions,
                'balance' => $balance,
                'form' => $form->createView()
            ]
        );
    }


    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     *
     */
    private function getFilters(Request $request): array
    {
        $filters = [];
        $filters['category_id'] = $request->query->getInt('filters_category_id');
        $filters['created_at'] = $request->query->getInt('filters_created_at');

        return $filters;
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
    #[Route(
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


        //dd($request, $form);
        if ($form->isSubmitted() && $form->isValid()) {

            // dd($request);
            $data = $form->getData(); //dane z formu
            $selectedentity = $data->getwallet();

            $sum = $data->getSum(); //sum z form
            $value = $data->isValue(); //value z form

            $a = $this->walletService->CountWalletSum($selectedentity, $sum, $value);
            if ($a = false) {
                $this->addFlash(
                    'notice',
                    'Value is to low');
            }
                $this->transactionService->save($transaction);

//                $this->addFlash(
//                    'success',
//                    $this->translator->trans('message.created_successfully')
//                );

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
         * @param Request $request HTTP request
         * @param Transaction $transaction Transaction entity
         *
         * @return Response HTTP response
         */
        #[
        Route('/{id}/delete', name: 'transaction_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('DELETE', subject: 'transaction')]
    public function delete(Request $request, Transaction $transaction): Response
    {
        $form = $this->createForm(FormType::class, $transaction, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('transaction_delete', ['id' => $transaction->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletService->delete($transaction);


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




    /**
     * @Route("/{date}", name="transaction_by_date")
     */
  #  public function getByDate(Request $request,TransactionRepository $transactionRepository, string $date): Response
  #  {
      #  $user = $this->getUser();
     #   $dateTime = new DateTime($date);

#        $pagination = $this->transactionService->getByDate(
#            $request->query->getInt('page', 1),
 #           $user,
 #           $dateTime
  #      );



  #      return $this->render('transaction/by_date.html.twig', [
      #      'date' => $dateTime,
  #          'pagination' => $pagination
  #      ]);
 #   }












}
