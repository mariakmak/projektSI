<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;
    private UserRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(UserRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em?->close();
        $this->em = null;
    }

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

    public function testQueryAllReturnsQueryableBuilder(): void
    {
        $qb = $this->repo->queryAll();
        $this->assertNotNull($qb->getQuery()->getResult());
        $this->assertTrue(true);
    }
}





