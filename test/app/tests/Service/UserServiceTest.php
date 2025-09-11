<?php

/**
 * user service tests.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserService;
use App\Service\UserServiceInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class UserServiceTest.
 *
 * @covers \App\Service\UserService
 */
class UserServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager = null;

    /**
     * user service.
     */
    private ?UserServiceInterface $userService = null;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->userService = $container->get(UserService::class);
    }

    /**
     * Tear down test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager?->close();
        $this->entityManager = null;
    }

    /**
     * Test saving a user with roles.
     *
     * @dataProvider provideUserRoles
     *
     * @param string $email Email of the user to create
     * @param array  $roles Roles to assign to the user
     */
    public function testSave(string $email, array $roles): void
    {
        // given
        $user = new User();
        $user->setEmail($email);
        $user->setPassword('test123');
        $user->setRoles($roles);

        // when
        $this->userService->save($user);

        // then
        $expectedUserId = $user->getId();
        $resultUser = $this->entityManager->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.id = :id')
            ->setParameter(':id', $expectedUserId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();
        $this->assertEquals($user, $resultUser);
        $this->assertEquals($roles, $resultUser->getRoles());
    }

    /**
     * Data provider for testSave.
     *
     * @return array<string, array<int, mixed>>
     */
    public function provideUserRoles(): array
    {
        return [
            'user_with_user_role' => ['user@example.com', ['ROLE_USER']],
            'user_with_admin_role' => ['admin@example.com', ['ROLE_ADMIN', 'ROLE_USER']],
        ];
    }

    /**
     * Test getting a paginated list of users.
     */
    public function testGetPaginatedList(): void
    {
        // given
        for ($i = 0; $i < 10; ++$i) {
            $user = new User();
            $user->setEmail('user'.$i.'@example.com');
            $user->setPassword('test123');
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();

        // when
        $pagination = $this->userService->getPaginatedList(1);

        // then
        $this->assertInstanceOf(PaginationInterface::class, $pagination);
        $this->assertGreaterThan(0, count($pagination));
    }
}
