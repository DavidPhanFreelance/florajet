<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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

    static function generateToken(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        //todo: form is not fully filled for saving in db
        //todo: form is not fully filled for saving in db
        //todo: form is not fully filled for saving in db

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('_preview_error');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #1st method to login using form - see AuthController
    #[Route(path: '/login', name: 'app_login')]
    public function loginForm(AuthenticationUtils $authenticationUtils): Response
    {
        $error = '';

        if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $user = $this->userRepository->findOneBy(['username' => $username]);

            if ($user && password_verify($password, $user->getPassword())) {
                // Créer un token d'authentification
                $token = new UsernamePasswordToken($user,'main', $user->getRoles());

                // Stocker le token dans le token storage
                $this->container->get('security.token_storage')->setToken($token);

                return $this->redirectToRoute('home');
            } else {
                $error = 'Identifiants invalides.';
            }
        }

        return $this->render('security/login.html.twig', [
            'error' => $error
        ]);
    }

    #2nd method to login using api
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

        return $this->json(['message' => 'Invalid credentials (login)'], Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(AuthenticationUtils $authenticationUtils): Response
    {
        // todo: clean session, tokens etc...

        return new Response('', Response::HTTP_OK);
    }

}