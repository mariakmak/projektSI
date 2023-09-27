<?php
/**
 * Categories service interface.
 */

namespace App\Service;

use App\Entity\Categories;
use App\Entity\User;
use App\Repository\CategoriesRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface CategoriesServiceInterface.
 */
interface CategoriesServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Categories $categories Categories entity
     */
    public function save(Categories $categories): void;

    /**
     * Delete entity.
     *
     * @param Categories $category Categories entity
     */
    public function delete(Categories $category): void;


    public function findOneById(int $id): ?CategoriesRepository;
}


