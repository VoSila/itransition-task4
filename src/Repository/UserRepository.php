<?php

namespace App\Repository;

use App\Controller\RegistrationController;
use App\Entity\User;
use App\Security\LoginFormAuthenticator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function encodePassword(User $user, FormInterface $form, UserPasswordHasherInterface $userPasswordHasher): void {
        $password = $form->get('plainPassword')->getData();
        $hashedPassword = $userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
    }

    public function setUserProperties(User $user): void {
        $user->setDateRegister(new \DateTime())
            ->setStatus(1);
    }

    public function saveUser(User $user, EntityManagerInterface $entityManager): void {
        $entityManager->persist($user);
        $entityManager->flush();
    }

    public function loginUser(User $user, Security $security): Response {
        return $security->login($user, LoginFormAuthenticator::class, 'main');
    }
}
