<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;




class CategoryVoter extends Voter
{


    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'VIEW';

    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'DELETE';

    /**
     * Security helper.
     *
     * @var Security
     */
    private Security $security;

    /**
     * OrderVoter constructor.
     *
     * @param Security $security Security helper
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }




    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::DELETE, self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Category;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        return false;
    }

    /**
     * Checks if user can edit category.
     *

     * @param User $user User
     *
     * @return bool Result
     */
    private function canEdit(Category $category, User $user): bool
    {
        return $category->getAuthor() === $user;
    }

    /**
     * Checks if user can view category.
     *
     * @param Category $category categories entity
     * @param User $user User
     *
     * @return bool Result
     */
    private function canView(Category $category, User $user): bool
    {
        return $category->getAuthor() === $user;
    }

    /**
     * Checks if user can delete category.
     *
     * @param Category $category category entity
     * @param User $user User
     *
     * @return bool Result
     */
    private function canDelete(Category $category, User $user): bool
    {
        return $category->getAuthor() === $user;
    }


}
