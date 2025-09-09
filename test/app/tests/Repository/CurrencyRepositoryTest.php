<?php

/**
 * This file is part of the [Your Project Name] package.
 */

namespace App\Tests\Repository;

use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Unit tests for the CurrencyRepository.
 */
class CurrencyRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;
    private CurrencyRepository $repo;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(CurrencyRepository::class);
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
     * Test adding and removing a currency.
     */
    public function testAddAndRemove(): void
    {
        $currency = new Currency();
        $currency->setName('PLN');
        $this->repo->add($currency, true);

        $found = $this->repo->findOneBy(['name' => 'PLN']);
        $this->assertNotNull($found);

        $this->repo->remove($currency, true);
        $this->assertNull($this->repo->findOneBy(['name' => 'PLN']));
    }
}
