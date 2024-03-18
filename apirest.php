// src/Controller/ApiRestController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiRestController extends AbstractController
{
    /**
    * @Route("/api/articles", name="api_articles", methods={"GET"})
    */
    public function getArticles(Request $request, ArticleAgregator $articleAgregator): JsonResponse
    {
        require_once __DIR__.'/index test .php';
        $articleAgregator   = new ArticleAgregator();
        $articles           = $articleAgregator->getArticles($request);

        return $this->json($articles);
    }
}