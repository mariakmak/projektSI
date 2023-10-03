<?php

/**
 * Category entity.
 */

namespace App\Entity;

use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class Category.
 */


#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'Category')]
class Category
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
    #[Assert\NotBlank]
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




    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type(User::class)]
    private ?User $author = null;











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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;


    }








}
