<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Afficher tous les produits avec pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with('category')->active();

            // Filtrage par catégorie
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Recherche par nom
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $products = $query->paginate($request->get('per_page', 20));

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des produits',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher les produits en vedette
     */
    public function featured(): JsonResponse
    {
        try {
            $products = Product::with('category')
                ->featured()
                ->active()
                ->inStock()
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des produits en vedette',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un produit spécifique
     */
    public function show($id): JsonResponse
    {
        try {
            $product = Product::with(['category'])
                ->active()
                ->findOrFail($id);

            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Produit non trouvé',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Recherche de produits
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');

            if (empty($query)) {
                return response()->json([
                    'data' => [],
                    'message' => 'Aucun terme de recherche fourni'
                ]);
            }

            $products = Product::with('category')
                ->active()
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                      ->orWhere('description', 'like', '%' . $query . '%')
                      ->orWhere('short_description', 'like', '%' . $query . '%');
                })
                ->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la recherche',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
