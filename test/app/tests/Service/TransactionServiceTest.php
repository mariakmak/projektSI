<?php

/**
 * Transaction service tests.
 */

namespace App\Tests\Service;

use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Entity\Category;
use App\Entity\Currency;
use App\Service\TransactionService;
use App\Service\TransactionServiceInterface;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TransactionServiceTest.
 *
 * @covers \App\Service\TransactionService
 */
class TransactionServiceTest extends KernelTestCase
{
    /**
     * Entity manager.
     */
    private ?EntityManagerInterface $entityManager = null;

    /**
     * Transaction service.
     */
    private ?TransactionServiceInterface $transactionService = null;

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
        $this->transactionService = $container->get(TransactionService::class);
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
     * Test saving a transaction.
     */
    public function testSave(): void
    {
        // given
        $user = new User();
        $user->setEmail('transaction@example.com');
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
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        $category = new Category();
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
        $transaction->setDescription('Test transaction description');
        $transaction->setAuthor($user);
        $transaction->setWallet($wallet);
        $transaction->setCategory($category);

        // when
        $this->transactionService->save($transaction);

        // then
        $expectedTransactionId = $transaction->getId();
        $resultTransaction = $this->entityManager->createQueryBuilder()
            ->select('transaction')
            ->from(Transaction::class, 'transaction')
            ->where('transaction.id = :id')
            ->setParameter(':id', $expectedTransactionId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($transaction, $resultTransaction);
    }

    /**
     * Test deleting a transaction.
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
        $wallet->setName('Delete Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(2000);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        $category = new Category();
        $category->setName('Delete Category');
        $category->setAuthor($user);
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $transaction = new Transaction();
        $transaction->setName('To Delete');
        $transaction->setSum(200);
        $transaction->setValue(false);
        $transaction->setDescription('To be deleted');
        $transaction->setAuthor($user);
        $transaction->setWallet($wallet);
        $transaction->setCategory($category);

        $this->transactionService->save($transaction);
        $id = $transaction->getId();

        // when
        $this->transactionService->delete($transaction);

        // then
        $found = $this->entityManager->createQueryBuilder()
            ->select('transaction')
            ->from(Transaction::class, 'transaction')
            ->where('transaction.id = :id')
            ->setParameter(':id', $id, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();
        $this->assertNull($found);
    }

    /**
     * Test getting a paginated list of transactions.
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
        $currency->setName('GBP');
        $this->entityManager->persist($currency);
        $this->entityManager->flush();

        $wallet = new Wallet();
        $wallet->setName('Paginate Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(5000);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        $category = new Category();
        $category->setName('Paginate Category');
        $category->setAuthor($user);
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        for ($i = 0; $i < 10; ++$i) {
            $transaction = new Transaction();
            $transaction->setName('Transaction '.$i);
            $transaction->setSum(100 * ($i + 1));
            $transaction->setValue(0 === $i % 2);
            $transaction->setDescription('Description '.$i);
            $transaction->setAuthor($user);
            $transaction->setWallet($wallet);
            $transaction->setCategory($category);
            $this->transactionService->save($transaction);
        }

        // when
        $result = $this->transactionService->getPaginatedList(1, $user);

        // then
        $this->assertPaginatedListStructure($result);

        // when
        $filters = ['category_id' => $category->getId()];
        $resultWithFilters = $this->transactionService->getPaginatedList(1, $user, $filters);

        // then
        $this->assertPaginatedListStructure($resultWithFilters);
    }

    /**
     * Test getting transactions filtered by date.
     */
    public function testGetByDate(): void
    {
        // given
        $user = new User();
        $user->setEmail('date@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $currency = new Currency();
        $currency->setName('PLN');
        $this->entityManager->persist($currency);
        $this->entityManager->flush();

        $wallet = new Wallet();
        $wallet->setName('Date Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(4000);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        $category = new Category();
        $category->setName('Date Category');
        $category->setAuthor($user);
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($category);
        $this->entityManager->flush();


        $transaction = new Transaction();
        $transaction->setName('Date Transaction');
        $transaction->setSum(300);
        $transaction->setValue(true);
        $transaction->setDescription('Date description');
        $transaction->setAuthor($user);
        $transaction->setWallet($wallet);
        $transaction->setCategory($category);
        $transaction->setCreatedAt(new \DateTimeImmutable('2024-01-15'));
        $this->transactionService->save($transaction);

        // when
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        $result = $this->transactionService->getByDate(1, $user, $startDate, $endDate);

        // then
        $this->assertPaginatedListStructure($result);
    }

    /**
     * Test getting transactions by date outside the range.
     */
    public function testGetByDateOutsideRange(): void
    {
        // given
        $user = new User();
        $user->setEmail('outside@example.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $currency = new Currency();
        $currency->setName('CHF');
        $this->entityManager->persist($currency);
        $this->entityManager->flush();

        $wallet = new Wallet();
        $wallet->setName('Outside Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(6000);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        $category = new Category();
        $category->setName('Outside Category');
        $category->setAuthor($user);
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));
        $this->entityManager->persist($category);
        $this->entityManager->flush();


        $transaction = new Transaction();
        $transaction->setName('Outside Range Transaction');
        $transaction->setSum(400);
        $transaction->setValue(false);
        $transaction->setDescription('Outside range description');
        $transaction->setAuthor($user);
        $transaction->setWallet($wallet);
        $transaction->setCategory($category);
        $transaction->setCreatedAt(new \DateTimeImmutable('2024-03-15'));
        $this->transactionService->save($transaction);

        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $result = $this->transactionService->getByDate(1, $user, $startDate, $endDate);

        // then
        $this->assertPaginatedListStructure($result);
    }

    /**
     * Assert paginated list structure.
     *
     * @param array $result The paginated result to check
     */
    private function assertPaginatedListStructure(array $result): void
    {
        $this->assertIsArray($result);
        $this->assertArrayHasKey('transactions', $result);
        $this->assertArrayHasKey('balance', $result);
        $this->assertInstanceOf(PaginationInterface::class, $result['transactions']);
        $this->assertIsFloat($result['balance']);
    }
}
