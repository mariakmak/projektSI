<?php

/**
 * This file is part of the [Your Project Name] package.
 */

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Unit tests for the UserRepository.
 */
class UserRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;
    private UserRepository $repo;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(UserRepository::class);
    }

    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em?->close();
        $this->em = null;
    }

    /**
     * Test adding and removing a user.
     */
    public function testAddAndRemove(): void
    {
        $user = new User();
        $user->setEmail('user1@example.com');
        $user->setPassword('pwd');

        $this->repo->add($user, true);

        $found = $this->repo->findOneBy(['email' => 'user1@example.com']);
        $this->assertNotNull($found);

        $this->repo->remove($user, true);
        $this->assertNull($this->repo->findOneBy(['email' => 'user1@example.com']));
    }

    /**
     * Test saving a user and upgrading the password.
     */
    public function testSaveAndUpgradePassword(): void
    {
        $user = new User();
        $user->setEmail('user2@example.com');
        $user->setPassword('old');

        $this->repo->save($user);

        $this->repo->upgradePassword($user, 'new');

        $reloaded = $this->repo->findOneBy(['email' => 'user2@example.com']);
        $this->assertSame('new', $reloaded->getPassword());
    }

    /**
     * Test that queryAll returns a valid QueryBuilder.
     */
    public function testQueryAllReturnsQueryableBuilder(): void
    {
        $qb = $this->repo->queryAll();
        $this->assertNotNull($qb->getQuery()->getResult());
        $this->assertTrue(true);
    }
}
