<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class AuthController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    private function generateToken(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->json(['message' => 'Missing username or password'], Response::HTTP_BAD_REQUEST);
        }

        $username = $data['username'];
        $password = $data['password'];

        $user = $this->userRepository->findOneBy(['username' => $username]);

        if ($user && password_verify($password, $user->getPassword())) {
            $token = $this->generateToken();
            $user->setToken($token);
            $this->entityManager->flush();

            return $this->json(['token' => $token]);
        }

        return $this->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): void
    {
        // logique de déco
    }

}