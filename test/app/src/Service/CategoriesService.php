<?php
/**
 * Categories service.
 */

namespace App\Service;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TaskService.
 */
class CategoriesService implements CategoriesServiceInterface
{
    /**
     * Task repository.
     */
    private CategoriesRepository $categoriesRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param CategoriesRepository  $categoriesRepository CategoriesRepository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(CategoriesRepository $categoriesRepository, PaginatorInterface $paginator)
    {
        $this->categoriesRepository = $categoriesRepository;
        $this->paginator = $paginator;
    }



    /**
     * Save entity.
     *
     * @param Categories $categories Categories entity
     */


    /**
     * Save entity.
     *
     * @param Categories $categories Categories entity
     */
    public function save(Categories $categories): void
    {
        if ($categories->getId() == null) {
            $categories->setCreatedAt(new \DateTimeImmutable());
        }
        $categories->setUpdatedAt(new \DateTimeImmutable());

        $this->categoriesRepository->save($categories);
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
            $this->categoriesRepository->queryAll(),
            $page,
            CategoriesRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }
}

