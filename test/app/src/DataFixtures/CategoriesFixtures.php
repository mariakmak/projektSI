<?php

/**
 * Categories fixtures.
 */

namespace App\DataFixtures;



use App\Entity\Categories;
use DateTimeImmutable;


/**
 * Class CategoriesFixtures.
 */
class CategoriesFixtures extends AppFixtures
{


    /**
     * Load data.
     */

    public function loadData(): void
    {
        $this->createMany(20, 'categories', function (int $i) {
            $category = new Categories();
            $category->setName($this->faker->sentence);
            $category->setCreatedAt(
                DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );
            $category->setUpdatedAt(
                DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );

            return $category;
        });

        $this->manager->flush();
    }
}