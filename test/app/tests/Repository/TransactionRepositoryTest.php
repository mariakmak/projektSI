<?php

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

class TransactionRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;
    private TransactionRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(TransactionRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em?->close();
        $this->em = null;
    }

    private function createUser(): User
    {
        $u = new User();
        $u->setEmail(uniqid('u').'@example.com');
        $u->setPassword('pwd');
        $this->em->persist($u);
        $this->em->flush();
        return $u;
    }

    private function createCurrency(string $name = 'USD'): Currency
    {
        $c = new Currency();
        $c->setName($name);
        $this->em->persist($c);
        $this->em->flush();
        return $c;
    }

    private function createCategory(User $author, string $name = null): Category
    {
        static $counter = 1;

        $c = new Category();
        $c->setName($name ?? 'category_' . $counter);

        $c->setAuthor($author);
        $c->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $c->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));

        $counter++;

        $this->em->persist($c);
        $this->em->flush();

        return $c;
    }

    private function createWallet(User $author, Currency $currency, string $name = null): Wallet
    {
        static $counter = 1;

        $w = new Wallet();
        $w->setName($name ?? 'wallet_' . $counter);
        $w->setCurrency($currency);
        $w->setAuthor($author);
        $w->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $w->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));
        $w->setSum(0);

        $counter++;

        $this->em->persist($w);
        $this->em->flush();
        return $w;
    }

    private function createTransaction(User $author, Wallet $wallet, Category $category, string $name = null, int $sum = 10, bool $value = true, string $date = '2024-02-01' ): Transaction
    {

        static $counter = 1;

        $t = new Transaction();
        $t->setName($name ?? 'transaction_' . $counter);
        $t->setDescription('desc');
        $t->setCreatedAt(new \DateTimeImmutable($date));
        $t->setSum($sum);
        $t->setValue($value);
        $t->setAuthor($author);
        $t->setWallet($wallet);
        $t->setCategory($category);

        $counter++;

        $this->repo->save($t);
        return $t;
    }

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
}





