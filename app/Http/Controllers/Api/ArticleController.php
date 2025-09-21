<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    /**
     * Afficher tous les articles publiés
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $articles = Article::published()
                ->recent($request->get('limit', 10))
                ->get();

            return response()->json($articles);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des articles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un article spécifique
     */
    public function show($id): JsonResponse
    {
        try {
            $article = Article::published()->findOrFail($id);

            return response()->json($article);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Article non trouvé',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
