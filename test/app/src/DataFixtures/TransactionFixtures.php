<?php
/**
 * Transaction fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\Transaction;
use App\Entity\Enum\TaskStatus;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Wallet;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class TransactionFixtures.
 */
class TransactionFixtures extends AppFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $this->createMany(100, 'transactions', function (int $i) {
            $transaction = new Transaction();

            /** @var Category $category */
            $category = $this->getRandomReference('categories');
            $transaction->setCategory($category);
            $transaction->setName($this->faker->sentence);

            $transaction->setDescription($this->faker->sentence);
            $transaction->setCreatedAt(
                DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $transaction->setSum($this->faker->biasedNumberBetween($min = 1, $max = 1000));
            $transaction->setValue($this->faker->boolean);

            /** @var Currency $currency */
            $currency = $this->getRandomReference('currencies');
            $transaction->setCurrency($currency);

            /** @var Wallet $wallet */
            $wallet = $this->getRandomReference('wallets');
            $transaction->setWallet($wallet);


            $author = $this->getRandomReference('users');
            $transaction->setAuthor($author);


            return $transaction;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     */
    public function getDependencies(): array
    {
        return [CategoryFixtures::class, CurrencyFixtures::class, WalletFixtures::class, UserFixtures::class  ];
    }
}