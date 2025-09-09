<?php

/**
 * User entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class UserTest.
 */
class UserTest extends KernelTestCase
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
     * Test User Entity.
     */
    public function testUserEntity(): void
    {
        // given
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('testpassword');
        $user->setRoles(['ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // when
        $expectedUser = new User();
        $expectedUser->setEmail('another@example.com');
        $expectedUser->setPassword($user->getPassword());
        $expectedUser->setRoles($user->getRoles());
        $this->entityManager->persist($expectedUser);
        $this->entityManager->flush();

        // then
        $this->assertFalse($expectedUser->getId() === $user->getId());
        $this->assertNotSame($expectedUser->getEmail(), $user->getEmail());
        $this->assertSame($expectedUser->getPassword(), $user->getPassword());
        $this->assertSame($expectedUser->getRoles(), $user->getRoles());
        $this->assertSame($expectedUser->getUserIdentifier(), $expectedUser->getEmail());
        $this->assertSame($user->getUserIdentifier(), $user->getEmail());
    }

    /**
     * Test User security methods.
     */
    public function testUserSecurityMethods(): void
    {
        // given
        $user = new User();
        $user->setEmail('security@example.com');
        $user->setPassword('test');

        // when & then
        $this->assertNull($user->getSalt());
        $user->eraseCredentials();
        $this->assertTrue(true);
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
