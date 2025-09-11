<?php

/**
 * This file is part of the [Your Project Name] package.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Unit tests for the CategoryRepository.
 */
class CategoryRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;
    private CategoryRepository $repo;

    /**
     * Set up the test environment.
     *
     * Boots the Symfony kernel and initializes the entity manager and repository.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(CategoryRepository::class);
    }

    /**
     * Tear down the test environment.
     *
     * Closes the entity manager and cleans up references.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em?->close();
        $this->em = null;
    }

    /**
     * Test adding, removing, saving, and deleting categories in the repository.
     */
    public function testAddRemoveSaveDelete(): void
    {
        $author = $this->createUser('a1@example.com');
        $category = new Category();
        $category->setName('C1');
        $category->setAuthor($author);
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));

        //        $this->repo->add($category, true);
        //        $this->assertNotNull($this->repo->findOneBy(['name' => 'C1']));
        //
        //        $this->repo->remove($category, true);
        //        $this->assertNull($this->repo->findOneBy(['name' => 'C1']));

        $this->repo->save($category);
        $this->assertNotNull($this->repo->findOneBy(['name' => 'C1']));

        $this->repo->delete($category);
        $this->assertNull($this->repo->findOneBy(['name' => 'C1']));
    }

    /**
     * Test repository queryByAuthor method.
     *
     * Ensures that only categories authored by a specific user are returned.
     */
    public function testQueryByAuthor(): void
    {
        $author1 = $this->createUser('u1@example.com');
        $author2 = $this->createUser('u2@example.com');

        $this->createCategory($author1, 'A');
        $this->createCategory($author1, 'B');
        $this->createCategory($author2, 'C');

        $qb = $this->repo->queryByAuthor($author1);
        $results = $qb->getQuery()->getResult();

        $this->assertGreaterThanOrEqual(2, count($results));
        foreach ($results as $cat) {
            $this->assertSame($author1->getId(), $cat->getAuthor()->getId());
        }
    }

    /**
     * Test repository queryAll method.
     *
     * Ensures that all categories are returned in descending order of updatedAt.
     */
    public function testQueryAll(): void
    {
        $author = $this->createUser('author@example.com');

        $cat1 = $this->createCategory($author, 'Cat1');
        $cat1->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));

        $cat2 = $this->createCategory($author, 'Cat2');
        $cat2->setUpdatedAt(new \DateTimeImmutable('2024-01-03'));

        $cat3 = $this->createCategory($author, 'Cat3');
        $cat3->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));


        $qb = $this->repo->queryAll();
        $results = $qb->getQuery()->getResult();

        $this->assertCount(3, $results);

        $this->assertSame('Cat2', $results[0]->getName()); // 2024-01-03
        $this->assertSame('Cat3', $results[1]->getName()); // 2024-01-02
        $this->assertSame('Cat1', $results[2]->getName()); // 2024-01-01
    }

    /**
     * Create and persist a user for testing.
     *
     * @param string $email Email address of the user (default: 'author@example.com')
     *
     * @return User The created user entity
     */
    private function createUser(string $email = 'author@example.com'): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword('pwd');
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Create and persist a category for testing.
     *
     * @param User        $author Author of the category
     * @param string|null $name   Optional name of the category
     *
     * @return Category The created Category entity
     */
    private function createCategory(User $author, ?string $name = null): Category
    {
        static $counter = 1;

        $c = new Category();
        $c->setAuthor($author);
        $c->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $c->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));

        $c->setName($name ?? 'category_'.$counter);

        ++$counter;

        $this->repo->save($c);

        return $c;
    }
}
