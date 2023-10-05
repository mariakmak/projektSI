<?php
/**
 * Category service.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Class CategoryService.
 */
class CategoryService implements CategoryServiceInterface
{
    /**
     * Category repository.
     */
    private CategoryRepository $categoryRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;



    /**
     * Transaction repository.
     */
    private TransactionRepository $transactionRepository;

    /**
     * Constructor.
     *
     * @param CategoryRepository  $categoryRepository CategoryRepository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(CategoryRepository $categoryRepository, PaginatorInterface $paginator, TransactionRepository $transactionRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->paginator = $paginator;
        $this->transactionRepository = $transactionRepository;
    }





    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void
    {
        if ($category->getId() == null) {
            $category->setCreatedAt(new \DateTimeImmutable());
        }
        $category->setUpdatedAt(new \DateTimeImmutable());

        $this->categoryRepository->save($category);
    }



    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
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
            $this->categoryRepository->queryByAuthor($author),
            $page,
            CategoryRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }


    /**
     * Find by id.
     *
     * @param int $id Category id
     *
     * @return Category|null Category entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?Category
    {
        return $this->categoryRepository->findOneById($id);
    }



    public function canBeDeleted(Category $category): bool
    {
        try {
            $result = $this->transactionRepository->queryByCategory($category);

            return !($result > 0);

        } catch (NoResultException|NonUniqueResultException) {
            return false;

        }
    }








}

