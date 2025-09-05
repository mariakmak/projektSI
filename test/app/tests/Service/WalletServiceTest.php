<?php

/**
 * Wallet service tests.
 * 
 * UWAGA: Metoda canBeDeleted w WalletService NIE sprawdza czy portfel może być usunięty!
 * Tylko usuwa wszystkie transakcje z portfela. Portfel pozostaje w bazie danych.
 * Nazwa metody jest myląca - w rzeczywistości robi clearTransactions().
 */

namespace App\Tests\Service;

use App\Entity\Wallet;
use App\Entity\Currency;
use App\Entity\Transaction;
use App\Service\WalletService;
use App\Service\WalletServiceInterface;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WalletServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager = null;

    /**
     * Wallet service.
     */
    private ?WalletServiceInterface $walletService = null;

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
        $this->walletService = $container->get(WalletService::class);
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

    public function testSave(): void
    {
        // given
        $user = new User();
        $user->setEmail('wallet@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $currency = new Currency();
        $currency->setName('USD');
        $this->entityManager->persist($currency);
        $this->entityManager->flush();
        
        $wallet = new Wallet();
        $wallet->setName('Test Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(1000);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        
        // when
        $this->walletService->save($wallet);

        // then
        $expectedWalletId = $wallet->getId();
        $resultWallet = $this->entityManager->createQueryBuilder()
            ->select('wallet')
            ->from(Wallet::class, 'wallet')
            ->where('wallet.id = :id')
            ->setParameter(':id', $expectedWalletId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($wallet, $resultWallet);
    }

    public function testDelete(): void
    {
        // given
        $user = new User();
        $user->setEmail('delete@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $currency = new Currency();
        $currency->setName('EUR');
        $this->entityManager->persist($currency);
        $this->entityManager->flush();
        
        $wallet = new Wallet();
        $wallet->setName('To Delete');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(500);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        
        $this->walletService->save($wallet);
        $id = $wallet->getId();
        
        // when
        $this->walletService->delete($wallet);
        
        // then
        $found = $this->entityManager->createQueryBuilder()
            ->select('wallet')
            ->from(Wallet::class, 'wallet')
            ->where('wallet.id = :id')
            ->setParameter(':id', $id, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();
        $this->assertNull($found);
    }

    public function testGetPaginatedList(): void
    {
        // given
        $user = new User();
        $user->setEmail('paginate@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $currency = new Currency();
        $currency->setName('USD');
        $this->entityManager->persist($currency);
        $this->entityManager->flush();
        

        for ($i = 0; $i < 10; $i++) {
            $wallet = new Wallet();
            $wallet->setName('Wallet ' . $i);
            $wallet->setCurrency($currency);
            $wallet->setAuthor($user);
            $wallet->setSum(100 * ($i + 1));
            $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
            $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
            $this->walletService->save($wallet);
        }
        
        // when
        $pagination = $this->walletService->getPaginatedList(1, $user);
        
        // then
        $this->assertInstanceOf(PaginationInterface::class, $pagination);
        $this->assertGreaterThan(0, count($pagination));
    }

    public function testCanBeDeleted(): void
    {
        // given
        $user = new User();
        $user->setEmail('deletable@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $currency = new Currency();
        $currency->setName('JPY');
        $this->entityManager->persist($currency);
        $this->entityManager->flush();
        

        $category = new \App\Entity\Category();
        $category->setName('Test Category');
        $category->setAuthor($user);
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        
        $wallet = new Wallet();
        $wallet->setName('Wallet with Transactions');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(1000);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->walletService->save($wallet);
        

        $transaction = new Transaction();
        $transaction->setName('Test Transaction');
        $transaction->setSum(100);
        $transaction->setValue(true);
        $transaction->setDescription('Test description');
        $transaction->setAuthor($user);
        $transaction->setWallet($wallet);
        $transaction->setCategory($category);
        $transaction->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
        

        $transactionsBefore = $this->entityManager->getRepository(Transaction::class)->findBy(['wallet' => $wallet]);
        $this->assertCount(1, $transactionsBefore);
        
        // when
        $this->walletService->canBeDeleted($wallet);
        
        // then
        $foundWallet = $this->entityManager->getRepository(Wallet::class)->find($wallet->getId());
        $this->assertNotNull($foundWallet);
        $this->assertEquals('Wallet with Transactions', $foundWallet->getName());

        $transactionsAfter = $this->entityManager->getRepository(Transaction::class)->findBy(['wallet' => $wallet]);
        $this->assertCount(0, $transactionsAfter);
    }

    public function testCanBeDeletedEmptyWallet(): void
    {
        // given
        $user = new User();
        $user->setEmail('empty@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $currency = new Currency();
        $currency->setName('GBP');
        $this->entityManager->persist($currency);
        $this->entityManager->flush();
        
        $wallet = new Wallet();
        $wallet->setName('Empty Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(0);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->walletService->save($wallet);
        
        // when
        $this->walletService->canBeDeleted($wallet);
        
        // then
        $foundWallet = $this->entityManager->getRepository(Wallet::class)->find($wallet->getId());
        $this->assertNotNull($foundWallet);
        $this->assertEquals('Empty Wallet', $foundWallet->getName());
        

        $transactions = $this->entityManager->getRepository(Transaction::class)->findBy(['wallet' => $wallet]);
        $this->assertCount(0, $transactions);
    }

    public function testCountWalletSum(): void
    {
        // given
        $user = new User();
        $user->setEmail('count@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $currency = new Currency();
        $currency->setName('PLN');
        $this->entityManager->persist($currency);
        $this->entityManager->flush();
        
        $wallet = new Wallet();
        $wallet->setName('Test Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(1000);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->walletService->save($wallet);
        
        // when
        $result = $this->walletService->countWalletSum($wallet, 500, true);
        
        // then
        $this->assertTrue($result);
        

        $this->assertEquals(1500, $wallet->getSum());
    }

    public function testCountWalletSumSubtract(): void
    {
        // given
        $user = new User();
        $user->setEmail('subtract@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $currency = new Currency();
        $currency->setName('CHF');
        $this->entityManager->persist($currency);
        $this->entityManager->flush();
        
        $wallet = new Wallet();
        $wallet->setName('Test Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(1000);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->walletService->save($wallet);
        
        // when
        $result = $this->walletService->countWalletSum($wallet, 300, false);
        
        // then
        $this->assertTrue($result);
        

        $this->assertEquals(700, $wallet->getSum());
    }
} 