<?php

/**
 * Category service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\User;
use App\Service\CategoryService;
use App\Service\CategoryServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Knp\Component\Pager\Pagination;


class CategoryServiceTest extends KernelTestCase
{
    /**
     * Category repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Category service.
     */
    private ?CategoryServiceInterface $categoryService;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->categoryService = $container->get(CategoryService::class);
    }





    /**
     * Test save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        // given
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $category = new Category();
        $category->setName('Test Category');
        $category->setAuthor($user);

        // when
        $this->categoryService->save($category);

        // then
        $expectedCategoryId = $category->getId();
        $resultCategory = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $expectedCategoryId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($category, $resultCategory);
    }

    /**
     * Test delete.
     *
     * @throws ORMException
     */
    public function testDelete(): void
    {
        // given
        $user = new User();
        $user->setEmail('delete@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $category = new Category();
        $category->setName('To Delete');
        $category->setAuthor($user);
        $this->categoryService->save($category);
        $id = $category->getId();
        
        // when
        $this->categoryService->delete($category);
        
        // then
        $found = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $id, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();
        $this->assertNull($found);
    }

    /**
     * Test find one by id.
     *
     * @throws ORMException
     */
    public function testFindOneById(): void
    {
        // given
        $user = new User();
        $user->setEmail('find@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $category = new Category();
        $category->setName('Find Me');
        $category->setAuthor($user);
        $this->categoryService->save($category);
        
        // when
        $found = $this->categoryService->findOneById($category->getId());
        
        // then
        $this->assertInstanceOf(Category::class, $found);
        $this->assertEquals($category, $found);
    }

    /**
     * Test get paginated list.
     *
     * @throws ORMException
     */
    public function testGetPaginatedList(): void
    {
        // given
        $user = new User();
        $user->setEmail('paginate@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        

        for ($i = 0; $i < 10; $i++) {
            $category = new Category();
            $category->setName('Cat ' . $i);
            $category->setAuthor($user);
            $this->categoryService->save($category);
        }
        
        // when
        $pagination = $this->categoryService->getPaginatedList(1, $user);
        
        // then
        $this->assertInstanceOf(Pagination\PaginationInterface::class, $pagination);
        $this->assertGreaterThan(0, count($pagination));
    }

    /**
     * Test can be deleted.
     *
     * @throws ORMException
     */
    public function testCanBeDeleted(): void
    {
        // given
        $user = new User();
        $user->setEmail('deletable@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $category = new Category();
        $category->setName('Deletable');
        $category->setAuthor($user);
        $this->categoryService->save($category);
        
        // when
        $result = $this->categoryService->canBeDeleted($category);
        
        // then
        $this->assertTrue($result);
    }


}
