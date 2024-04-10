<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Source;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/articles', name: 'app_api_articles_')]
class ApiController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ArticleRepository $articleRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                ArticleRepository $articleRepository
    ) {
        $this->entityManager = $entityManager;
        $this->articleRepository = $articleRepository;
    }

    #[Route('/', name: 'get_all')]
    public function index(): JsonResponse
    {
        $articles = $this->entityManager->getRepository(Article::class)->findAll();
        $articleArray = [];
        foreach ($articles as $article) {
            $source = $this->entityManager->getRepository(Source::class)->find($article->getSourceId());
            $articleArray[] = [
                'id' => $article->getId(),
                'subject' => $article->getName(),
                'source' => $source->getName(),
                'content' => $article->getContent(),
                'date' => $article->getDate(),
                'author' => $article->getAuthor(),
            ];
        }

        return $this->json($articleArray);
    }

    #[Route('/{id}', name: 'get_by_id')]
    #[IsGranted('ROLE_USER')]
    public function getArticleById($id): JsonResponse
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            return new JsonResponse(['error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $source = $this->entityManager->getRepository(Source::class)->find($article->getSourceId());
        $articleArray[] = [
            'id' => $article->getId(),
            'subject' => $article->getName(),
            'source' => $source->getName(),
            'content' => $article->getContent(),
            'date' => $article->getDate(),
            'author' => $article->getAuthor(),
        ];

        return new JsonResponse($articleArray);
    }

    #[Route('/date/{date}/{type}', name: 'get_by_date')]
    public function getArticlesByDate(string $date, string $type): JsonResponse
    {
        $dateTime = new \DateTime($date);
        $articles = $this->articleRepository->findByDateFilter($dateTime, $type);
        $articleArray = [];

        if (!$articles) {
            return new JsonResponse(['error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }
        foreach ($articles as $article) {
            $source = $this->entityManager->getRepository(Source::class)->find($article->getSourceId());
            $articleArray[] = [
                'id' => $article->getId(),
                'subject' => $article->getName(),
                'source' => $source->getName(),
                'content' => $article->getContent(),
                'date' => $article->getDate(),
                'author' => $article->getAuthor(),
            ];
        }

        return $this->json($articleArray);
    }

    #[Route('/search/{name}', name: 'get_by_name')]
    public function getByName(ArticleRepository $articleRepository, $name): JsonResponse
    {
        $articles = $articleRepository->findByName($name);
        $articleArray = [];

        if (!$articles) {
            return new JsonResponse(['error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }
        foreach ($articles as $article) {
            $source = $this->entityManager->getRepository(Source::class)->find($article->getSourceId());
            $articleArray[] = [
                'id' => $article->getId(),
                'subject' => $article->getName(),
                'source' => $source->getName(),
                'content' => $article->getContent(),
                'date' => $article->getDate(),
                'author' => $article->getAuthor(),
            ];
        }

        return $this->json($articleArray);
    }

    #[Route('/author/{name}', name: 'get_by_author')]
    public function getByAuthor(ArticleRepository $articleRepository, $name): JsonResponse
    {
        $articles = $articleRepository->findByAuthor($name);
        $articleArray = [];

        if (!$articles) {
            return new JsonResponse(['error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }
        foreach ($articles as $article) {
            $source = $this->entityManager->getRepository(Source::class)->find($article->getSourceId());
            $articleArray[] = [
                'id' => $article->getId(),
                'subject' => $article->getName(),
                'source' => $source->getName(),
                'content' => $article->getContent(),
                'date' => $article->getDate(),
                'author' => $article->getAuthor(),
            ];
        }

        return $this->json($articleArray);
    }

    #[Route('/source/{name}', name: 'get_by_source')]
    public function getBySource(ArticleRepository $articleRepository, $name): JsonResponse
    {
        $articles = $articleRepository->findBySource($name);
        $articleArray = [];

        if (!$articles) {
            return new JsonResponse(['error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }
        foreach ($articles as $article) {
            $source = $this->entityManager->getRepository(Source::class)->find($article->getSourceId());
            $articleArray[] = [
                'id' => $article->getId(),
                'subject' => $article->getName(),
                'source' => $source->getName(),
                'content' => $article->getContent(),
                'date' => $article->getDate(),
                'author' => $article->getAuthor(),
            ];
        }

        return $this->json($articleArray);
    }

}