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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class WalletServiceTest.
 *
 * @covers \App\Service\WalletService
 */
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

    /**
     * Test saving a wallet.
     */
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

    /**
     * Test deleting a wallet.
     */
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

    /**
     * Test getting a paginated list of wallets.
     */
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


        for ($i = 0; $i < 10; ++$i) {
            $wallet = new Wallet();
            $wallet->setName('Wallet '.$i);
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

    /**
     * Test canBeDeleted.
     */
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
        $result = $this->walletService->canBeDeleted($wallet);

        // then
        $this->assertFalse($result, 'This wallet contains transactions');

        $transactionsAfter = $this->entityManager->getRepository(Transaction::class)->findBy(['wallet' => $wallet]);
        $this->assertCount(1, $transactionsAfter, 'Transactions should still exist');
    }

    /**
     * Test canBeDeleted on an empty wallet (no transactions).
     */
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
        $result = $this->walletService->canBeDeleted($wallet);

        // then
        $this->assertTrue($result, 'Empty wallet is deletable');

        $foundWallet = $this->entityManager->getRepository(Wallet::class)->find($wallet->getId());
        $this->assertNotNull($foundWallet);
        $this->assertEquals('Empty Wallet', $foundWallet->getName());

        $transactions = $this->entityManager->getRepository(Transaction::class)->findBy(['wallet' => $wallet]);
        $this->assertCount(0, $transactions, 'No transactions should exist');
    }

    /**
     * Test updateBalanceOnTransactionCreate.
     *
     *  - value=true → wallet sum should increase
     *  - value=false → wallet sum should decrease
     *  - subtraction larger than wallet balance → should return false
     *  - wallet=null → should return false
     */
    public function testUpdateBalanceOnTransactionCreate(): void
    {
        // given
        $user = new User();
        $user->setEmail('user1@example.com');
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

        $category = new \App\Entity\Category();
        $category->setName('Test Category');
        $category->setAuthor($user);
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $transaction = new Transaction();
        $transaction->setName('Test Transaction');
        $transaction->setSum(100);
        $transaction->setValue(true);
        $transaction->setDescription('Income');
        $transaction->setAuthor($user);
        $transaction->setWallet($wallet);
        $transaction->setCategory($category);
        $transaction->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        // when
        $result = $this->walletService->updateBalanceOnTransactionCreate($transaction);

        // then
        $this->assertTrue($result);
        $this->assertEquals(1100, $wallet->getSum(), 'Wallet sum should increase');

        // value=false
        $transaction->setValue(false);
        $transaction->setSum(30);
        $result = $this->walletService->updateBalanceOnTransactionCreate($transaction);

        $this->assertTrue($result);
        $this->assertEquals(1070, $wallet->getSum(), 'Wallet sum should decrease');

        // test gdy odejmowanie przekroczyłoby sumę portfela
        $transaction->setSum(10000);
        $result = $this->walletService->updateBalanceOnTransactionCreate($transaction);

        $this->assertFalse($result, 'Should return false if wallet sum would go below 0');

        // brak portfela
        $transaction->setWallet(null);
        $transaction->setSum(50);
        $transaction->setValue(true);
        $result = $this->walletService->updateBalanceOnTransactionCreate($transaction);

        $this->assertFalse($result, 'Should return false if wallet is null');
    }

    /**
     * Test updateBalanceOnTransactionDelete.
     *
     *  - value=true → wallet sum should decrease
     *  - value=false → wallet sum should increase
     *  - wallet=null → wallet sum remains unchanged
     */
    public function testUpdateBalanceOnTransactionDelete(): void
    {
        // given
        $user = new User();
        $user->setEmail('user1@example.com');
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

        $category = new \App\Entity\Category();
        $category->setName('Test Category');
        $category->setAuthor($user);
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $transaction = new Transaction();
        $transaction->setName('Test Transaction');
        $transaction->setSum(100);
        $transaction->setValue(true);
        $transaction->setDescription('Income');
        $transaction->setAuthor($user);
        $transaction->setWallet($wallet);
        $transaction->setCategory($category);
        $transaction->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        // when:
        $this->walletService->updateBalanceOnTransactionDelete($transaction);

        // value=true
        $this->assertEquals(900, $wallet->getSum(), 'Wallet sum should decrease');

        // value=false
        $wallet->setSum(100);
        $transaction->setValue(false);
        $transaction->setSum(100);
        $this->walletService->updateBalanceOnTransactionDelete($transaction);

        $this->assertEquals(200, $wallet->getSum(), 'Wallet sum should increase');

        // brak portfela
        $transaction->setWallet(null);
        $wallet->setSum(100);
        $this->walletService->updateBalanceOnTransactionDelete($transaction);

        $this->assertEquals(100, $wallet->getSum(), 'Wallet sum should remain unchanged if wallet is null');
    }
}
