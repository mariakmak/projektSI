<?php
/**
 * Categories controller.
 */

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoriesController.
 */
#[Route('/categories')]
class CategoriesController extends AbstractController
{
    /**
     * Index action.
     *
     *
     * @param Request            $request        HTTP Request
     * @param PaginatorInterface $paginator      Paginator
     * @param CategoriesRepository $categoriesRepository categories repository
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'category_index',
        methods: 'GET'
    )]
    public function index(Request $request, CategoriesRepository $categoriesRepository, PaginatorInterface $paginator): Response
    {

        $pagination = $paginator->paginate(
            $categoriesRepository->queryAll(),
            $request->query->getInt('page', 1),
            TaskRepository::PAGINATOR_ITEMS_PER_PAGE
        );


        $categories = $categoriesRepository->findAll();

        return $this->render(
            'category/index.html.twig',
            ['categories' => $categories, 'pagination' => $pagination]
        );
    }

    /**
     * Show action.
     *
     * @param Task $task Task entity
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
}