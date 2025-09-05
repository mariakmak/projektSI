<?php

/**
 * Transaction entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Currency;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TransactionTest.
 */
class TransactionTest extends KernelTestCase
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
     * Test Transaction Entity.
     */
    public function testTransactionEntity(): void
    {
        // given
        $category = new Category();
        $category->setName('Food');
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));

        $user = new User();
        $user->setEmail('u@example.com');
        $user->setPassword('user123');

        $category->setAuthor($user);

        $currency = new Currency();
        $currency->setName('EUR');

        $wallet = new Wallet();
        $wallet->setName('W');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));
        $wallet->setSum(0);

        $this->entityManager->persist($category);
        $this->entityManager->persist($user);
        $this->entityManager->persist($currency);
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        $transaction = new Transaction();
        $transaction->setName('Lunch');
        $transaction->setDescription('desc');
        $transaction->setCreatedAt(new \DateTimeImmutable('2024-02-01'));
        $transaction->setSum(50);
        $transaction->setValue(true);
        $transaction->setCategory($category);
        $transaction->setWallet($wallet);
        $transaction->setAuthor($user);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        // when
        $expectedTransaction = new Transaction();
        $expectedTransaction->setName('Dinner');
        $expectedTransaction->setDescription($transaction->getDescription());
        $expectedTransaction->setCreatedAt(new \DateTimeImmutable('2024-02-02'));
        $expectedTransaction->setSum($transaction->getSum());
        $expectedTransaction->setValue($transaction->getValue());
        $expectedTransaction->setCategory($transaction->getCategory());
        $expectedTransaction->setWallet($transaction->getWallet());
        $expectedTransaction->setAuthor($transaction->getAuthor());
        $this->entityManager->persist($expectedTransaction);
        $this->entityManager->flush();

        // then
        $this->assertFalse($expectedTransaction->getId() === $transaction->getId());
        $this->assertNotSame($expectedTransaction->getName(), $transaction->getName());
        $this->assertSame($expectedTransaction->getDescription(), $transaction->getDescription());
        $this->assertSame($expectedTransaction->getSum(), $transaction->getSum());
        $this->assertSame($expectedTransaction->getValue(), $transaction->getValue());
        $this->assertSame($expectedTransaction->getCategory(), $transaction->getCategory());
        $this->assertSame($expectedTransaction->getWallet(), $transaction->getWallet());
        $this->assertSame($expectedTransaction->getAuthor(), $transaction->getAuthor());
    }



    /**
     * Test that unique constraint prevents duplicate transaction names for same user.
     */
    public function testCannotCreateTransactionWithSameNameForSameUser(): void
    {
        // given
        $category = new Category();
        $category->setName('Food');
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('test');

        $category->setAuthor($user);

        $currency = new Currency();
        $currency->setName('USD');

        $wallet = new Wallet();
        $wallet->setName('Test Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setSum(0);

        $this->entityManager->persist($category);
        $this->entityManager->persist($user);
        $this->entityManager->persist($currency);
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        $transaction1 = new Transaction();
        $transaction1->setName('Lunch');
        $transaction1->setDescription('desc');
        $transaction1->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $transaction1->setSum(50);
        $transaction1->setValue(true);
        $transaction1->setCategory($category);
        $transaction1->setWallet($wallet);
        $transaction1->setAuthor($user);
        
        $this->entityManager->persist($transaction1);
        $this->entityManager->flush();

        // when
        $transaction2 = new Transaction();
        $transaction2->setName('Lunch');
        $transaction2->setDescription('desc2');
        $transaction2->setCreatedAt(new \DateTimeImmutable('2024-01-02'));
        $transaction2->setSum(30);
        $transaction2->setValue(false);
        $transaction2->setCategory($category);
        $transaction2->setWallet($wallet);
        $transaction2->setAuthor($user);

        // then
        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);
        
        $this->entityManager->persist($transaction2);
        $this->entityManager->flush();
    }

    /**
     * Test that same transaction name can exist for different users.
     */
    public function testSameTransactionNameCanExistForDifferentUsers(): void
    {
        // given
        $category = new Category();
        $category->setName('Food');
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));

        $user1 = new User();
        $user1->setEmail('user1@example.com');
        $user1->setPassword('test1');

        $user2 = new User();
        $user2->setEmail('user2@example.com');
        $user2->setPassword('test2');

        $category->setAuthor($user1);

        $currency = new Currency();
        $currency->setName('EUR');

        $wallet1 = new Wallet();
        $wallet1->setName('Wallet 1');
        $wallet1->setCurrency($currency);
        $wallet1->setAuthor($user1);
        $wallet1->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet1->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet1->setSum(0);

        $wallet2 = new Wallet();
        $wallet2->setName('Wallet 2');
        $wallet2->setCurrency($currency);
        $wallet2->setAuthor($user2);
        $wallet2->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet2->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet2->setSum(0);

        $this->entityManager->persist($category);
        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($currency);
        $this->entityManager->persist($wallet1);
        $this->entityManager->persist($wallet2);
        $this->entityManager->flush();

        $transaction1 = new Transaction();
        $transaction1->setName('Lunch');
        $transaction1->setDescription('desc1');
        $transaction1->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $transaction1->setSum(50);
        $transaction1->setValue(true);
        $transaction1->setCategory($category);
        $transaction1->setWallet($wallet1);
        $transaction1->setAuthor($user1);

        $transaction2 = new Transaction();
        $transaction2->setName('Lunch');
        $transaction2->setDescription('desc2');
        $transaction2->setCreatedAt(new \DateTimeImmutable('2024-01-02'));
        $transaction2->setSum(30);
        $transaction2->setValue(false);
        $transaction2->setCategory($category);
        $transaction2->setWallet($wallet2);
        $transaction2->setAuthor($user2);

        // when & then
        $this->entityManager->persist($transaction1);
        $this->entityManager->persist($transaction2);
        $this->entityManager->flush();

        $this->assertNotSame($transaction1->getId(), $transaction2->getId());
        $this->assertSame($transaction1->getName(), $transaction2->getName());
        $this->assertNotSame($transaction1->getAuthor(), $transaction2->getAuthor());
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





