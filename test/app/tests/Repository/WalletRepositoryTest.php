<?php

/**
 * This file is part of the [Your Project Name] package.
 */

namespace App\Tests\Repository;

use App\Entity\Currency;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Unit tests for the CurrencyRepository.
 */
class WalletRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;
    private WalletRepository $repo;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(WalletRepository::class);
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
     * Test adding, saving, and deleting a Wallet entity.
     */
    public function testAddRemoveSaveDelete(): void
    {
        $author = $this->createUser();
        $currency = $this->createCurrency('PLN');

        $wallet = new Wallet();
        $wallet->setName('W1');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($author);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-01-01'));

        //        $this->repo->add($wallet, true);
        //        $this->assertNotNull($this->repo->findOneBy(['name' => 'W1']));
        //
        //        $this->repo->remove($wallet, true);
        //        $this->assertNull($this->repo->findOneBy(['name' => 'W1']));

        $this->repo->save($wallet);
        $this->assertNotNull($this->repo->findOneBy(['name' => 'W1']));

        $this->repo->delete($wallet);
        $this->assertNull($this->repo->findOneBy(['name' => 'W1']));
    }

    /**
     * Test querying wallets by author.
     */
    public function testQueryByAuthor(): void
    {
        $author1 = $this->createUser();
        $author2 = $this->createUser();
        $currency = $this->createCurrency('EUR');

        $this->createWallet($author1, $currency, 'A');
        $this->createWallet($author1, $currency, 'B');
        $this->createWallet($author2, $currency, 'C');

        $qb = $this->repo->queryByAuthor($author1);
        $results = $qb->getQuery()->getResult();

        $this->assertEquals(2, count($results));
        foreach ($results as $w) {
            $this->assertSame($author1->getId(), $w->getAuthor()->getId());
        }
    }

    /**
     * Create and persist a new user entity.
     *
     * @return User the created user
     */
    private function createUser(): User
    {
        $user = new User();
        $user->setEmail(uniqid('u').'@example.com');
        $user->setPassword('pwd');
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Create and persist a new Currency entity.
     *
     * @param string $name the currency name (default 'USD')
     *
     * @return Currency the created currency
     */
    private function createCurrency(string $name = 'USD'): Currency
    {
        $currency = new Currency();
        $currency->setName($name);
        $this->em->persist($currency);
        $this->em->flush();

        return $currency;
    }

    /**
     * Create and persist a new Wallet entity.
     *
     * @param User        $author   the wallet owner
     * @param Currency    $currency the wallet currency
     * @param string|null $name     optional name for the wallet
     *
     * @return Wallet the created wallet
     */
    private function createWallet(User $author, Currency $currency, ?string $name = null): Wallet
    {
        static $counter = 1;

        $wallet = new Wallet();
        $wallet->setName($name ?? 'wallet_'.$counter);
        $wallet->setCurrency($currency);
        $wallet->setAuthor($author);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-01-02'));
        $wallet->setSum(0);

        ++$counter;

        $this->repo->save($wallet);

        return $wallet;
    }
}
