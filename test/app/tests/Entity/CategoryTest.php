<?php

/**
 * Category entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryTest.
 */
class CategoryTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Set up test.
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * Test Category Entity.
     */
    public function testCategoryEntity(): void
    {
        // given
        $user = new User();
        $user->setEmail('author@example.com');
        $user->setPassword('testpassword');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $category = new Category();
        $category->setName('Test Category');
        $category->setAuthor($user);
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // when
        $expectedCategory = new Category();
        $expectedCategory->setName('Another Category');
        $expectedCategory->setAuthor($category->getAuthor());
        $expectedCategory->setCreatedAt($category->getCreatedAt());
        $expectedCategory->setUpdatedAt($category->getUpdatedAt());
        $this->entityManager->persist($expectedCategory);
        $this->entityManager->flush();

        // then
        $this->assertFalse($expectedCategory->getId() === $category->getId());
        $this->assertNotSame($expectedCategory->getName(), $category->getName());
        $this->assertSame($expectedCategory->getAuthor(), $category->getAuthor());
        $this->assertSame($expectedCategory->getCreatedAt(), $category->getCreatedAt());
        $this->assertSame($expectedCategory->getUpdatedAt(), $category->getUpdatedAt());
    }

    /**
     * Test Category with different author.
     */
    public function testCategoryWithDifferentAuthor(): void
    {
        // given
        $user1 = new User();
        $user1->setEmail('author1@example.com');
        $user1->setPassword('test1');

        $user2 = new User();
        $user2->setEmail('author2@example.com');
        $user2->setPassword('test2');

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->flush();

        $category1 = new Category();
        $category1->setName('Category 1');
        $category1->setAuthor($user1);
        $category1->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category1->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));

        $category2 = new Category();
        $category2->setName('Category 2');
        $category2->setAuthor($user2);
        $category2->setCreatedAt(new \DateTimeImmutable('2024-01-02'));
        $category2->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));

        $this->entityManager->persist($category1);
        $this->entityManager->persist($category2);
        $this->entityManager->flush();

        // when & then
        $this->assertNotSame($category1->getAuthor(), $category2->getAuthor());
        $this->assertSame($user1->getId(), $category1->getAuthor()->getId());
        $this->assertSame($user2->getId(), $category2->getAuthor()->getId());
    }

    /**
     * Test that unique constraint prevents duplicate category names for same user.
     */
    public function testCannotCreateCategoryWithSameNameForSameUser(): void
    {
        // given
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $category1 = new Category();
        $category1->setName('Food');
        $category1->setAuthor($user);
        $category1->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category1->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));

        $this->entityManager->persist($category1);
        $this->entityManager->flush();

        // when
        $category2 = new Category();
        $category2->setName('Food');
        $category2->setAuthor($user);
        $category2->setCreatedAt(new \DateTimeImmutable('2024-01-02'));
        $category2->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));

        // then
        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);

        $this->entityManager->persist($category2);
        $this->entityManager->flush();
    }

    /**
     * Test that same category name can exist for different users.
     */
    public function testSameCategoryNameCanExistForDifferentUsers(): void
    {
        // given
        $user1 = new User();
        $user1->setEmail('user1@example.com');
        $user1->setPassword('test1');

        $user2 = new User();
        $user2->setEmail('user2@example.com');
        $user2->setPassword('test2');

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->flush();

        $category1 = new Category();
        $category1->setName('Food');
        $category1->setAuthor($user1);
        $category1->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category1->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));

        $category2 = new Category();
        $category2->setName('Food');
        $category2->setAuthor($user2);
        $category2->setCreatedAt(new \DateTimeImmutable('2024-01-02'));
        $category2->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));

        // when & then
        $this->entityManager->persist($category1);
        $this->entityManager->persist($category2);
        $this->entityManager->flush();

        $this->assertNotSame($category1->getId(), $category2->getId());
        $this->assertSame($category1->getName(), $category2->getName());
        $this->assertNotSame($category1->getAuthor(), $category2->getAuthor());
    }

    /**
     * Reset the environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
