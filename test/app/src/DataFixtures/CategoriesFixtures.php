<?php

/**
 * Categories fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Categories;
use DateTimeImmutable;
use Faker\Factory;
use Faker\Generator;


/**
 * Class CategoriesFixtures.
 */
class CategoriesFixtures extends Fixture
{


    /**
     * Faker.
     *
     * @var Generator
     */
    protected Generator $faker;

    /**
     * Persistence object manager.
     *
     * @var ObjectManager
     */
    protected ObjectManager $manager;

    /**
     * Load.
     *
     * @param ObjectManager $manager Persistence object manager
     */

    public function load(ObjectManager $manager): void
    {

        $this->faker = Factory::create();

        for ($i = 0; $i < 10; ++$i) {
            $category = new Categories();
            $category->setName($this->faker->sentence);
            $category->setCreatedAt(
                DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );
            $category->setUpdatedAt(
                DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );
            $manager->persist($category);
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
