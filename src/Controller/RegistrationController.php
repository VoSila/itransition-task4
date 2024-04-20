<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        EntityManagerInterface      $entityManager,
        Request                     $request,
        Security                    $security,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processRegistration($form, $entityManager, $security, $userPasswordHasher, $request);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    private function processRegistration(
        EntityManagerInterface      $entityManager,
        FormInterface               $form,
        Security                    $security,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response
    {
        $user = $form->getData();
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

        if ($existingUser) {
            $this->addFlash('error', 'This email is already in use.');

            return $this->redirectToRoute('app_register');
        }
        $this->userRepository->encodePassword($user, $form, $userPasswordHasher);

        $this->userRepository->setUserProperties($user);

        $this->userRepository->saveUser($user, $entityManager);

        return $this->userRepository->loginUser($user, $security);
    }
}
