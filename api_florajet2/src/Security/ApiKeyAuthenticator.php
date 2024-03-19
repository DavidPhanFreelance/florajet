<?php
// src/Security/ApiKeyAuthenticator.php
// src/Security/ApiKeyAuthenticator.php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        if ( !$request->headers->has('X-AUTH-TOKEN') ) {
            $authorizationHeader = $request->headers->get('Authorization');
            return $authorizationHeader && preg_match('/^Bearer\s+(.*?)$/', $authorizationHeader);
        }
        return false;
    }

    public function authenticate(Request $request): Passport
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $credentials = null;

        if ($authorizationHeader && preg_match('/^Bearer\s+(.*?)$/', $authorizationHeader, $matches)) {
            $credentials = $matches[1];
        }

        if (!$this->isApiKeyValid($credentials)) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $user = $this->userRepository->findOneBy(['token' => $credentials]);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('User not found.');
        }

        return new Passport(new UserBadge($user->getUsername()), new PasswordCredentials($credentials, null));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        dd("success");
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
//        var_dump($exception->getMessageKey());
        $authorizationHeader = $request->headers->get('Authorization');
        dd($request->headers->get('Authorization'));


        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    private function isApiKeyValid(string $apiKey): bool
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['token' => $apiKey]);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('APIKEYAUTHENTICATOR: Invalid API key.');
        }

        return true;
    }
}