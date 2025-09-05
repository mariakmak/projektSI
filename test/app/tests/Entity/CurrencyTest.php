<?php

/**
 * Currency entity tests.
 */

namespace App\Tests\Entity;

use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CurrencyTest.
 */
class CurrencyTest extends KernelTestCase
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
     * Test Currency Entity.
     */
    public function testCurrencyEntity(): void
    {
        // given
        $currency = new Currency();
        $currency->setName('PLN');

        $this->entityManager->persist($currency);
        $this->entityManager->flush();

        // when
        $expectedCurrency = new Currency();
        $expectedCurrency->setName('USD');
        $this->entityManager->persist($expectedCurrency);
        $this->entityManager->flush();

        // then
        $this->assertFalse($expectedCurrency->getId() === $currency->getId());
        $this->assertNotSame($expectedCurrency->getName(), $currency->getName());
    }

    /**
     * Test multiple currencies.
     */
    public function testMultipleCurrencies(): void
    {
        // given
        $currencies = ['USD', 'EUR', 'GBP', 'JPY'];

        foreach ($currencies as $currencyName) {
            $currency = new Currency();
            $currency->setName($currencyName);
            $this->entityManager->persist($currency);
        }
        $this->entityManager->flush();

        // when
        $foundCurrencies = $this->entityManager->getRepository(Currency::class)->findAll();

        // then
        $this->assertGreaterThanOrEqual(count($currencies), count($foundCurrencies));
        
        foreach ($currencies as $currencyName) {
            $found = $this->entityManager->getRepository(Currency::class)->findOneBy(['name' => $currencyName]);
            $this->assertNotNull($found);
            $this->assertSame($currencyName, $found->getName());
        }
    }

    /**
     * Test that unique constraint prevents duplicate currency names.
     */
    public function testCannotCreateCurrencyWithSameName(): void
    {
        // given
        $currency1 = new Currency();
        $currency1->setName('PLN');
        $this->entityManager->persist($currency1);
        $this->entityManager->flush();

        // when
        $currency2 = new Currency();
        $currency2->setName('PLN');

        // then
        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);
        
        $this->entityManager->persist($currency2);
        $this->entityManager->flush();
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





