<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserListener
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
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
