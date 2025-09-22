<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductReviewController extends Controller
{
    public function index(Request $request, Product $product)
    {
        $validated = $request->validate([
            'page'        => 'integer|min:1',
            'perPage'     => 'integer|min:1|max:50',
            'sort'        => Rule::in(['recent','top','rating_desc','rating_asc']),
            'rating'      => 'nullable|integer|min:1|max:5',
            'with_photos' => 'nullable|boolean',
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['perPage'] ?? 10;
        $sort = $validated['sort'] ?? 'recent';
        $rating = $validated['rating'] ?? null;
        $withPhotos = filter_var($validated['with_photos'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $query = $product->reviews()->with(['user','photos']);

        if ($rating) $query->where('rating', $rating);
        if ($withPhotos) $query->whereHas('photos');

        switch ($sort) {
            case 'top':         $query->orderByDesc('helpful_count')->orderByDesc('id'); break;
            case 'rating_desc': $query->orderByDesc('rating')->orderByDesc('id'); break;
            case 'rating_asc':  $query->orderBy('rating')->orderByDesc('id'); break;
            case 'recent':
            default:            $query->orderByDesc('created_at'); break;
        }

        $total = (clone $query)->count();
        $items = $query->forPage($page, $perPage)->get();

        // Summary (sur tous les avis du produit, pas seulement filtrés)
        $summaryQuery = Review::where('product_id', $product->id);
        $average = round((float) $summaryQuery->avg('rating'), 1);
        $counts = Review::select('rating', DB::raw('COUNT(*) as c'))
            ->where('product_id', $product->id)
            ->groupBy('rating')
            ->pluck('c', 'rating');

        $dist = [];
        for ($i=5; $i>=1; $i--) { $dist[$i] = (int) ($counts[$i] ?? 0); }

        $withPhotosCount = Review::where('product_id', $product->id)->whereHas('photos')->count();

        return response()->json([
            'items'   => ReviewResource::collection($items),
            'total'   => (int) $total,
            'average' => (float) $average,
            'counts'  => $dist,
            'withPhotosCount' => (int) $withPhotosCount,
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $this->middleware('auth:sanctum');

        $user = $request->user();

        $validated = $request->validate([
            'rating'    => 'required|integer|min:1|max:5',
            'comment'   => 'required|string|min:20|max:2000',
            'anonymous' => 'boolean',
            'photos.*'  => 'nullable|image|max:5120', // 5MB
        ]);

        // TODO: Gate "canPostReview" si achat vérifié (placeholder)
        // Gate::authorize('post-review', [$user, $product]);

        DB::beginTransaction();
        try {
            $review = Review::create([
                'user_id'    => $user->id,
                'product_id' => $product->id,
                'rating'     => $validated['rating'],
                'comment'    => $validated['comment'],
                'anonymous'  => (bool) ($validated['anonymous'] ?? false),
                'verified'   => false, // TODO si achat vérifié
            ]);

            // Photos
            if ($request->hasFile('photos')) {
                $files = array_slice($request->file('photos'), 0, 5);
                foreach ($files as $file) {
                    $path = $file->store('reviews', 'public');
                    ReviewPhoto::create([
                        'review_id' => $review->id,
                        'path'      => Storage::url($path),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Avis publié avec succès',
                'review'  => new ReviewResource($review->load(['user','photos'])),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'avis',
            ], 500);
        }
    }
}
