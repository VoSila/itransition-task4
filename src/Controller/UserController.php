<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserService    $userService
    )
    {
    }

    #[Route('/table', name: 'app_table')]
    public function index(Security $security, UrlGeneratorInterface $urlGenerator,): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('default');
        }

        $status = $user->getStatus();

        if ($status == 2) {
            $logoutUrl = $urlGenerator->generate('app_logout');

            return new RedirectResponse($logoutUrl);
        }

        return $this->render('table/index.html.twig', [
            'users' => $this->userRepository->findAll(),
        ]);
    }

    #[Route('/block_users', name: 'block_users')]
    public function unblockUsers(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $userIds = $requestData['userIds'];

        if ($userIds[0] == null) {
            $userIds = array_slice($userIds, 1); //TODO убрать в сборе чекбоксов
        }

        try {
            $this->userService->block($userIds);
            $updatedUsers = $this->userRepository->findAll();

            return new JsonResponse(['users' => ($this->userService->jsonUsers($updatedUsers)), 'status' => true]);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => false], 403);
        }
    }

    #[Route('/unblock_users', name: 'unblock_users')]
    public function blockUsers(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $userIds = $requestData['userIds'];

        $this->userService->unblock($userIds);
        $updatedUsers = $this->userRepository->findAll();;

        return new JsonResponse(['users' => $this->userService->jsonUsers($updatedUsers)]);
    }

    #[Route('/delete_users', name: 'delete_users')]
    public function deleteUsers(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $userIds = $requestData['userIds'];

        try {
            $this->userService->delete($userIds);
            $updatedUsers = $this->userRepository->findAll();;

            return new JsonResponse(['users' => ($this->userService->jsonUsers($updatedUsers)), 'status' => true]);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => false], 403);
        }
    }
}
