<?php

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class UserService
{
    public function __construct(
        private readonly Security               $security,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function block(array $userIds): void
    {
        $authorisedUser = $this->security->getUser();
        $userHasBeenBlocked = false;

        foreach ($userIds as $userId) {
            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if (!$user) {
                continue;
            }

            if ($authorisedUser->getUserIdentifier() === $user->getEmail()){
                $userHasBeenBlocked = true;
            }

            $user->setStatus(2);
            $this->entityManager->flush();
        }

        if ($userHasBeenBlocked) {
            $this->security->logout();
            throw new \LogicException('You blocked', 403);
        }
    }

    public function unblock(array $userIds): void
    {
        foreach ($userIds as $userId) {
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if ($user) {
                $user->setStatus(1);
                $this->entityManager->flush();
            }
        }
    }

    public function delete(array $userIds): void
    {
        $authorisedUser = $this->security->getUser();
        $userHasBeenBlocked = false;

        foreach ($userIds as $userId) {
            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if (!$user) {
                continue;
            }

            if ($authorisedUser->getUserIdentifier() === $user->getEmail()){
                $userHasBeenBlocked = true;
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        if ($userHasBeenBlocked) {
            $this->security->logout();
            throw new \LogicException('User deleted', 403);
        }
    }

    public function jsonUsers($users): array
    {
        $updatedUsersArray = [];
        foreach ($users as $user) {
            $userData = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'dateRegister' => $user->getDateRegister()->format('Y-m-d H:i:s'),
                'dateLastLogin' => $user->getDateLastLogin()->format('Y-m-d H:i:s'),
                'status' => $user->getStatus()
            ];
            $updatedUsersArray[] = $userData;
        }

        return $updatedUsersArray;
    }
}
