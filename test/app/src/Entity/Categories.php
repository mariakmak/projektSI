<?php

/**
 * Category entity.
 */

namespace App\Entity;

use App\Repository\CategoriesRepository;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class Category.
 */


#[ORM\Entity(repositoryClass: CategoriesRepository::class)]
#[ORM\Table(name: 'Categories')]
class Categories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Name.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * Created at.
     *
     * @var DateTimeImmutable|null
     *
     */

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Updated at.
     *
     * @var DateTimeImmutable|null
     *
     */

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;


    /**
     * Getter for Id.
     *
     * @return int|null Id
     */

    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * Getter for name.
     *
     * @return string|null Name
     */

    public function getName(): ?string
    {
        return $this->name;
    }



    /**
     * Setter for name.
     *
     * @param string|null $title Name
     */
    public function setName(string $name): void
    {
        $this->name = $name;


    }



    /**
     * Getter for created at.
     *
     * @return DateTimeImmutable|null Created at
     */

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }


    /**
     * Setter for created at.
     *
     * @param DateTimeImmutable|null $createdAt Created at
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;


    }

    /**
     * Getter for updated at.
     *
     * @return DateTimeImmutable|null Updated at
     */

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }


    /**
     * Setter for updated at.
     *
     * @param DateTimeImmutable|null $updatedAt Updated at
     */

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;


    }
}
