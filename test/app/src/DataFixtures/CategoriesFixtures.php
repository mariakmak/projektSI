<?php

/**
 * Categories fixtures.
 */

namespace App\DataFixtures;



use App\Entity\Categories;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


/**
 * Class CategoriesFixtures.
 */
class CategoriesFixtures extends AppFixtures implements DependentFixtureInterface
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


            $author = $this->getRandomReference('users');
            $category->setAuthor($author);

            return $category;
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
        return [ UserFixtures::class, ];
    }




}