<?php
/**
 * Wallet controller.
 */

namespace App\Controller;

use App\Entity\Wallet;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;

use App\Service\WalletServiceInterface;
use App\Form\Type\WalletType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class WalletController.
 */
#[Route('/wallet')]
class WalletController extends AbstractController
{
    /**
     * Wallet service.
     */
    private WalletServiceInterface $walletService;

    /**
     * Constructor.
     */

    /**
     * Translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;


    /**
     * Constructor.
     *
     * @param WalletServiceInterface $walletService Wallet service
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(WalletServiceInterface $walletService, TranslatorInterface $translator)
    {
        $this->walletService = $walletService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     * @return Response HTTP response
     */
    #[Route(
        name: 'wallet_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {
        $pagination = $this->walletService->getPaginatedList(
            $request->query->getInt('page', 1),
            $this->getUser()
        );

        return $this->render(
            'wallet/index.html.twig',
            ['pagination' => $pagination]
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
    #[IsGranted('VIEW', subject: 'wallet')]
    public function show(Wallet $wallet): Response
    {
        return $this->render(
            'wallet/show.html.twig',
            ['wallet' => $wallet]
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
        name: 'wallet_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {

        /** @var User $user */
        $user = $this->getUser();
        $wallet = new Wallet();
        $wallet->setAuthor($user);

        $form = $this->createForm(WalletType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form ->getData(); //dane z formu
            $sum = $data->getSum();

            if($sum === null){
                $wallet->setSum(0);
                $this->walletService->save($wallet);
            }

            else{$this->walletService->save($wallet);}


            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render(
            'wallet/create.html.twig',
            ['form' => $form->createView()]
        );

    }


    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Wallet $wallet Wallet entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'wallet_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('EDIT', subject: 'wallet')]
    public function edit(Request $request, Wallet $wallet): Response
    {
        $form = $this->createForm(
            WalletType::class,
            $wallet,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('wallet_edit', ['id' => $wallet->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form ->getData(); //dane z formu
            $sum = $data->getSum();

            if($sum === null){
                $wallet->setSum(0);
                $this->walletService->save($wallet);
            }

            else{$this->walletService->save($wallet);}

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render(
            'wallet/edit.html.twig',
            [
                'form' => $form->createView(),
                'wallet' => $wallet,
            ]
        );
    }


    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Wallet $wallet Wallet entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'wallet_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('DELETE', subject: 'wallet')]
    public function delete(Request $request, Wallet $wallet, TransactionRepository $transaction): Response
    {
        $form = $this->createForm(FormType::class, $wallet, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('wallet_delete', ['id' => $wallet->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletService->delete($wallet, $transaction);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render(
            'wallet/delete.html.twig',
            [
                'form' => $form->createView(),
                'wallet' => $wallet,
            ]
        );
    }
}