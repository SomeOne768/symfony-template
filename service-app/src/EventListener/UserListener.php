<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserListener
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function prePersist(User $user): void
    {
        $this->encode($user);
    }

    public function preUpdate(User $user): void
    {
        $this->encode($user);
    }

    public function encode(User $user): void
    {
        if (null === $user->getPlainPassword()) {
            return;
        }

        $user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));
    }
}
