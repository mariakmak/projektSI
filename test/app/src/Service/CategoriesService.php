<?php
/**
 * Categories service.
 */

namespace App\Service;

use App\Entity\Categories;
use App\Entity\User;
use App\Repository\CategoriesRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class CategoriesService.
 */
class CategoriesService implements CategoriesServiceInterface
{
    /**
     * Categories repository.
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
    public function save(Categories $categories): void
    {
        if ($categories->getId() == null) {
            $categories->setCreatedAt(new \DateTimeImmutable());
        }
        $categories->setUpdatedAt(new \DateTimeImmutable());

        $this->categoriesRepository->save($categories);
    }



    public function delete(Categories $category): void
    {
        $this->categoriesRepository->delete($category);
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
            $this->categoriesRepository->queryByAuthor($author),
            $page,
            CategoriesRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }


    /**
     * Find by id.
     *
     * @param int $id Category id
     *
     * @return Categories|null Categories entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?CategoriesRepository
    {
        return $this->categoriesRepository->findOneById($id);
    }






}

