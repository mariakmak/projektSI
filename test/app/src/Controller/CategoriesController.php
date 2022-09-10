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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * Constructor.
     */
    public function __construct(CategoriesServiceInterface $taskService)
    {
        $this->categoriesService = $taskService;
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

            return $this->redirectToRoute('category_index');
        }

        return $this->render(
            'category/create.html.twig',
            ['form' => $form->createView()]
        );
    }



}

