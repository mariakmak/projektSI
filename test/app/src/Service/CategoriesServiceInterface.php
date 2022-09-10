<?php
/**
 * Categories service interface.
 */

namespace App\Service;

use App\Entity\Categories;
use Knp\Component\Pager\Pagination\PaginationInterface;

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
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Categories $categories Categories entity
     */
    public function save(Categories $categories): void;

}


