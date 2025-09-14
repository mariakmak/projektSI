<?php

/**
 * This file is part of the [Your Project Name] package.
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Entity\Currency;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Unit tests for the TransactionRepository.
 */
class TransactionRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;
    private TransactionRepository $repo;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(TransactionRepository::class);
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
     * Test adding, removing, saving, and deleting a transaction.
     */
    public function testAddRemoveSaveDelete(): void
    {
        $author = $this->createUser();
        $currency = $this->createCurrency();
        $wallet = $this->createWallet($author, $currency);
        $category = $this->createCategory($author);

        $t = new Transaction();
        $t->setName('TT');
        $t->setCreatedAt(new \DateTimeImmutable('2024-02-01'));
        $t->setSum(5);
        $t->setValue(true);
        $t->setAuthor($author);
        $t->setWallet($wallet);
        $t->setCategory($category);

        //        $this->repo->add($t, true);
        //        $this->assertNotNull($this->repo->findOneBy(['name' => 'TT']));
        //
        //        $this->repo->remove($t, true);
        //        $this->assertNull($this->repo->findOneBy(['name' => 'TT']));

        $this->repo->save($t);
        $this->assertNotNull($this->repo->findOneBy(['name' => 'TT']));

        $this->repo->delete($t);
        $this->assertNull($this->repo->findOneBy(['name' => 'TT']));
    }

    /**
     * Test querying transactions by author and wallet.
     */
    public function testQueryByAuthorAndWallet(): void
    {
        $author1 = $this->createUser();
        $author2 = $this->createUser();
        $currency = $this->createCurrency();
        $wallet1 = $this->createWallet($author1, $currency, 'W1');
        $wallet2 = $this->createWallet($author2, $currency, 'W2');
        $category1 = $this->createCategory($author1, 'C1');
        $category2 = $this->createCategory($author2, 'C2');

        $this->createTransaction($author1, $wallet1, $category1, 'T1');
        $this->createTransaction($author1, $wallet1, $category1, 'T2');
        $this->createTransaction($author2, $wallet2, $category2, 'T3');

        $qb1 = $this->repo->queryByAuthor($author1);
        $r1 = $qb1->getQuery()->getResult();
        $this->assertEquals(2, count($r1));
        foreach ($r1 as $t) {
            $this->assertSame($author1->getId(), $t->getAuthor()->getId());
        }

        $qb2 = $this->repo->queryByWallet($wallet1);
        $r2 = $qb2->getQuery()->getResult();
        foreach ($r2 as $t) {
            $this->assertSame($wallet1->getId(), $t->getWallet()->getId());
        }
    }

    /**
     * Test counting transactions by category.
     */
    public function testQueryByCategoryCount(): void
    {
        $author = $this->createUser();
        $currency = $this->createCurrency();
        $wallet = $this->createWallet($author, $currency);
        $category = $this->createCategory($author);

        $this->createTransaction($author, $wallet, $category, 'T1');
        $this->createTransaction($author, $wallet, $category, 'T2');

        $count = $this->repo->queryByCategory($category);
        $this->assertEquals(2, (int) $count);
    }

    /**
     * Test finding transactions within a specific date range.
     */
    public function testFindByDate(): void
    {
        $author = $this->createUser();
        $currency = $this->createCurrency();
        $wallet = $this->createWallet($author, $currency);
        $category = $this->createCategory($author);

        $this->createTransaction($author, $wallet, $category, 'A', 10, true, '2024-02-10');
        $this->createTransaction($author, $wallet, $category, 'B', 10, true, '2024-02-15');
        $this->createTransaction($author, $wallet, $category, 'C', 10, true, '2024-03-01');

        $start = new \DateTimeImmutable('2024-02-01');
        $end = new \DateTimeImmutable('2024-02-28');
        $results = $this->repo->findByDate($start, $end, $author);
        $this->assertEquals(2, count($results));
        foreach ($results as $t) {
            $this->assertSame($author->getId(), $t->getAuthor()->getId());
            $this->assertTrue($t->getCreatedAt() >= $start && $t->getCreatedAt() <= $end);
        }
    }

    /**
     * Test calculating total amount for a collection of transactions.
     */
    public function testCalculateTotalAmount(): void
    {
        $author = $this->createUser();
        $currency = $this->createCurrency();
        $wallet = $this->createWallet($author, $currency);
        $category = $this->createCategory($author);

        $t1 = $this->createTransaction($author, $wallet, $category, 'inc', 100, true);
        $t2 = $this->createTransaction($author, $wallet, $category, 'exp', 40, false);

        $collection = new ArrayCollection([$t1, $t2]);
        $total = $this->repo->calculateTotalAmount($collection);
        $this->assertSame(60.0, $total);
    }

    /**
     * Create a new user for testing.
     *
     * @return User The created user entity
     */
    private function createUser(): User
    {
        $u = new User();
        $u->setEmail(uniqid('u').'@example.com');
        $u->setPassword('pwd');
        $this->em->persist($u);
        $this->em->flush();

        return $u;
    }

    /**
     * Create a new currency for testing.
     *
     * @param string $name Name of the currency
     *
     * @return Currency The created currency entity
     */
    private function createCurrency(string $name = 'USD'): Currency
    {
        $c = new Currency();
        $c->setName($name);
        $this->em->persist($c);
        $this->em->flush();

        return $c;
    }

    /**
     * Create a new category for testing.
     *
     * @param User        $author Author of the category
     * @param string|null $name   Optional category name
     *
     * @return Category The created category entity
     */
    private function createCategory(User $author, ?string $name = null): Category
    {
        static $counter = 1;

        $c = new Category();
        $c->setName($name ?? 'category_'.$counter);

        $c->setAuthor($author);
        $c->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $c->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));

        ++$counter;

        $this->em->persist($c);
        $this->em->flush();

        return $c;
    }

    /**
     * Create a new wallet for testing.
     *
     * @param User        $author   Owner of the wallet
     * @param Currency    $currency Currency of the wallet
     * @param string|null $name     Wallet name
     *
     * @return Wallet The created wallet entity
     */
    private function createWallet(User $author, Currency $currency, ?string $name = null): Wallet
    {
        static $counter = 1;

        $w = new Wallet();
        $w->setName($name ?? 'wallet_'.$counter);
        $w->setCurrency($currency);
        $w->setAuthor($author);
        $w->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $w->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));
        $w->setSum(0);

        ++$counter;

        $this->em->persist($w);
        $this->em->flush();

        return $w;
    }

    /**
     * Create a new transaction for testing.
     *
     * @param User        $author   Author of the transaction
     * @param Wallet      $wallet   Wallet used in the transaction
     * @param Category    $category Category of the transaction
     * @param string|null $name     Optional transaction name
     * @param int         $sum      Amount
     * @param bool        $value    True for income, false for expense
     * @param string      $date     Transaction date in 'Y-m-d' format
     *
     * @return Transaction The created transaction entity
     */
    private function createTransaction(User $author, Wallet $wallet, Category $category, ?string $name = null, int $sum = 10, bool $value = true, string $date = '2024-02-01'): Transaction
    {

        static $counter = 1;

        $t = new Transaction();
        $t->setName($name ?? 'transaction_'.$counter);
        $t->setDescription('desc');
        $t->setCreatedAt(new \DateTimeImmutable($date));
        $t->setSum($sum);
        $t->setValue($value);
        $t->setAuthor($author);
        $t->setWallet($wallet);
        $t->setCategory($category);

        ++$counter;

        $this->repo->save($t);

        return $t;
    }
}
