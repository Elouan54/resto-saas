<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RestaurantResourceVoter extends Voter
{
    public const VIEW = 'RESOURCE_VIEW';
    public const EDIT = 'RESOURCE_EDIT';
    public const DELETE = 'RESOURCE_DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [
            self::VIEW,
            self::EDIT,
            self::DELETE
        ]) && method_exists($subject, 'getRestaurant');
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // ADMIN peut tout voir
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        $restaurant = $subject->getRestaurant();

        if (!$restaurant) {
            return false;
        }

        return $restaurant->getOwner() === $user;
    }
}