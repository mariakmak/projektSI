<?php

/**
 * Wallet entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Currency;
use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class WalletTest.
 */
class WalletTest extends KernelTestCase
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
     * Test Wallet Entity.
     */
    public function testWalletEntity(): void
    {
        // given
        $currency = new Currency();
        $currency->setName('USD');

        $user = new User();
        $user->setEmail('author@example.com');
        $user->setPassword('testpassword');

        $this->entityManager->persist($currency);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $wallet = new Wallet();
        $wallet->setName('Main Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $wallet->setSum(1000);

        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        // when
        $expectedWallet = new Wallet();
        $expectedWallet->setName('Secondary Wallet');
        $expectedWallet->setCurrency($wallet->getCurrency());
        $expectedWallet->setAuthor($wallet->getAuthor());
        $expectedWallet->setCreatedAt($wallet->getCreatedAt());
        $expectedWallet->setUpdatedAt($wallet->getUpdatedAt());
        $expectedWallet->setSum($wallet->getSum());
        $this->entityManager->persist($expectedWallet);
        $this->entityManager->flush();

        // then
        $this->assertFalse($expectedWallet->getId() === $wallet->getId());
        $this->assertNotSame($expectedWallet->getName(), $wallet->getName());
        $this->assertSame($expectedWallet->getCurrency(), $wallet->getCurrency());
        $this->assertSame($expectedWallet->getAuthor(), $wallet->getAuthor());
        $this->assertSame($expectedWallet->getCreatedAt(), $wallet->getCreatedAt());
        $this->assertSame($expectedWallet->getUpdatedAt(), $wallet->getUpdatedAt());
        $this->assertSame($expectedWallet->getSum(), $wallet->getSum());
    }

    /**
     * Test that unique constraint prevents duplicate wallet names for same user.
     */
    public function testCannotCreateWalletWithSameNameForSameUser(): void
    {
        // given
        $currency = new Currency();
        $currency->setName('EUR');

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('test');

        $this->entityManager->persist($currency);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $wallet1 = new Wallet();
        $wallet1->setName('Main Wallet');
        $wallet1->setCurrency($currency);
        $wallet1->setAuthor($user);
        $wallet1->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet1->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet1->setSum(0);

        $this->entityManager->persist($wallet1);
        $this->entityManager->flush();

        // when
        $wallet2 = new Wallet();
        $wallet2->setName('Main Wallet');
        $wallet2->setCurrency($currency);
        $wallet2->setAuthor($user);
        $wallet2->setCreatedAt(new \DateTimeImmutable('2024-01-02'));
        $wallet2->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));
        $wallet2->setSum(0);

        // then
        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);

        $this->entityManager->persist($wallet2);
        $this->entityManager->flush();
    }

    /**
     * Test that same wallet name can exist for different users.
     */
    public function testSameWalletNameCanExistForDifferentUsers(): void
    {
        // given
        $currency = new Currency();
        $currency->setName('USD');

        $user1 = new User();
        $user1->setEmail('user1@example.com');
        $user1->setPassword('test1');

        $user2 = new User();
        $user2->setEmail('user2@example.com');
        $user2->setPassword('test2');

        $this->entityManager->persist($currency);
        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->flush();

        $wallet1 = new Wallet();
        $wallet1->setName('Main Wallet');
        $wallet1->setCurrency($currency);
        $wallet1->setAuthor($user1);
        $wallet1->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet1->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet1->setSum(0);

        $wallet2 = new Wallet();
        $wallet2->setName('Main Wallet');
        $wallet2->setCurrency($currency);
        $wallet2->setAuthor($user2);
        $wallet2->setCreatedAt(new \DateTimeImmutable('2024-01-02'));
        $wallet2->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));
        $wallet2->setSum(0);

        // when & then
        $this->entityManager->persist($wallet1);
        $this->entityManager->persist($wallet2);
        $this->entityManager->flush();

        $this->assertNotSame($wallet1->getId(), $wallet2->getId());
        $this->assertSame($wallet1->getName(), $wallet2->getName());
        $this->assertNotSame($wallet1->getAuthor(), $wallet2->getAuthor());
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
