<?php
/**
 * Categories controller.
 */

namespace App\Controller;

use App\Entity\Categories;
use App\Form\Type\CategoriesType;
use App\Repository\CategoriesRepository;
use App\Service\CategoriesServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CategoriesController.
 */
#[Route('/categories')]
class CategoriesController extends AbstractController
{

    /**
     * Categories service.
     */
    private CategoriesServiceInterface $categoriesService;


    /**
     * Translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param CategoriesServiceInterface $taskService Task service
     * @param TranslatorInterface      $translator  Translator
     */



    /**
     * Constructor.
     */
    public function __construct(CategoriesServiceInterface $taskService, TranslatorInterface $translator)
    {
        $this->categoriesService = $taskService;
        $this->translator = $translator;
    }


    /**
     * Index action.
     *
     * @param Request            $request        HTTP Request
     * @return Response HTTP response
     */
    #[Route(
        name: 'category_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {

        $pagination = $this->categoriesService->getPaginatedList(
            $request->query->getInt('page', 1)

        );



        return $this->render(
            'category/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * Show action.
     *
     * @param Categories $categories Categories entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'category_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Categories $categories): Response
    {


        return $this->render(
            'category/show.html.twig',
            ['category' => $categories]
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
        name: 'category_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        $categories = new Categories();
        $form = $this->createForm(CategoriesType::class, $categories);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoriesService->save($categories);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('category_index');
        }

        return $this->render(
            'category/create.html.twig',
            ['form' => $form->createView()]
        );
    }


    /**
     * Delete action.
     *
     * @param Request  $request  HTTP request
     * @param Categories $category Category entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'category_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, Categories $category): Response
    {
        $form = $this->createForm(FormType::class, $category, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('category_delete', ['id' => $category->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoriesService->delete($category);

          /*  $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            ); */

            return $this->redirectToRoute('category_index');
        }

        return $this->render(
            'category/delete.html.twig',
            [
                'form' => $form->createView(),
                'category' => $category,
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Categories $category Categories entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'category_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Categories $category): Response
    {
        $form = $this->createForm(
            CategoriesType::class,
            $category,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('category_edit', ['id' => $category->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoriesService->save($category);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('category_index');
        }

        return $this->render(
            'category/edit.html.twig',
            [
                'form' => $form->createView(),
                'category' => $category,
            ]
        );
    }




}

