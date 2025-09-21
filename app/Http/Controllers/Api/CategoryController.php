<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Afficher toutes les catégories
     */
    public function index(): JsonResponse
    {
        try {
            $categories = Category::with('children')
                ->parent()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des catégories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher les catégories populaires
     */
    public function hot(): JsonResponse
    {
        try {
            $categories = Category::hot()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des catégories populaires',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une catégorie spécifique
     */
    public function show($id): JsonResponse
    {
        try {
            $category = Category::with(['children', 'products' => function($query) {
                $query->active()->take(12);
            }])->findOrFail($id);

            return response()->json($category);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Catégorie non trouvée',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
