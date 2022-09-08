<?php
/**
 * Wallet fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\Transaction;
use App\Entity\Enum\TaskStatus;
use App\Entity\Wallet;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class WalletFixtures.
 */
class WalletFixtures extends AppFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $this->createMany(5, 'wallets', function (int $i) {
            $wallet = new Wallet();



            $wallet->setName($this->faker->word);
            $wallet->setCreatedAt(
                DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            $wallet->setUpdatedAt(
                DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );


            /** @var Currency $currency */
            $currency = $this->getRandomReferences('currencies', 4);
            foreach ($currency as $elem) {
                $wallet->addCurrency($elem);
            }


            return $wallet;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     */
    public function getDependencies(): array
    {
        return [CurrencyFixtures::class];
    }


}