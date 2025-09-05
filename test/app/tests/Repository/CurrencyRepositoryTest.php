<?php

namespace App\Tests\Repository;

use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CurrencyRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;
    private CurrencyRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repo = $container->get(CurrencyRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em?->close();
        $this->em = null;
    }

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





