<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        // Rafraîchir l'utilisateur en chargeant ses données à partir du stockage
        return $this->entityManager->getRepository(User::class)->find($user->getId());
    }

    public function supportsClass(string $class): bool
    {
        // Indiquer si cette classe est prise en charge par ce fournisseur d'utilisateurs
        return User::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        //debug: var_dump('loadUserByIdentifier' ); IS OK
        return $this->entityManager->getRepository(User::class)->findOneBy(['username' => $identifier]);
    }
}