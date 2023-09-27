<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Transaction;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class TransactionVoter extends Voter
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
        return in_array($attribute, [self::EDIT, self::DELETE,  self::VIEW])
            && $subject instanceof \App\Entity\Transaction;
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
     * Checks if user can edit transaction.
     *

     * @param User $user User
     *
     * @return bool Result
     */
    private function canEdit(Transaction $transaction, User $user): bool
    {
        return $transaction->getAuthor() === $user;
    }

    /**
     * Checks if user can view transaction.
     *
     * @param Transaction $transaction transaction entity
     * @param User $user User
     *
     * @return bool Result
     */
    private function canView(Transaction $transaction, User $user): bool
    {
        return $transaction->getAuthor() === $user;
    }

    /**
     * Checks if user can delete transaction.
     *
     * @param Transaction $transaction transaction entity
     * @param User $user User
     *
     * @return bool Result
     */
    private function canDelete(Transaction $transaction, User $user): bool
    {
        return $transaction->getAuthor() === $user;
    }


}
