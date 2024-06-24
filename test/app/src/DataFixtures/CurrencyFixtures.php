<?php

/**
 * Currency fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Currency;

/**
 * Class CurrencyFixtures.
 */
class CurrencyFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     */
    public function loadData(): void
    {
        $this->createMany(20, 'currencies', function (int $i) {
            $currency = new Currency();
            $currency->setName($this->faker->currencyCode);

            return $currency;
        });

        $this->manager->flush();
    }
}
