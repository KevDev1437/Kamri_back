<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use App\Services\PurchaseVerificationService;
use App\Services\ReviewSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReviewsController extends Controller
{
    public function index(Request $req, Product $product, ReviewSummaryService $summaryService)
    {
        $q = $product->reviews()->where('status', 'published');

        if ($rating = $req->query('rating')) {
            $q->where('rating', (int) $rating);
        }
        if ($req->boolean('with_photos')) {
            $q->whereNotNull('photos')->whereJsonLength('photos', '>', 0);
        }

        $sort = $req->query('sort', 'recent');
        match ($sort) {
            'top' => $q->orderByDesc('helpful_count'),
            'rating_desc' => $q->orderByDesc('rating'),
            'rating_asc' => $q->orderBy('rating'),
            default => $q->orderByDesc('id'),
        };

        $perPage = (int) $req->query('perPage', 10);
        $page = (int) $req->query('page', 1);
        $paginator = $q->with(['user', 'votes', 'reports'])->paginate($perPage, ['*'], 'page', $page);

        // Résumé (cache)
        $summary = $summaryService->getSummary($product->id);

        return response()->json([
            'success' => true,
            'items' => ReviewResource::collection($paginator->items()),
            'total' => $paginator->total(),
            'average' => $summary['average'],
            'counts' => $summary['counts'],
            'withPhotosCount' => $summary['withPhotosCount'],
        ]);
    }

    public function store(StoreReviewRequest $req, Product $product, PurchaseVerificationService $pvs, ReviewSummaryService $summaryService)
    {
        $user = $req->user();

        if (!$pvs->userHasPurchasedProduct($user, $product->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les acheteurs peuvent laisser un avis.'
            ], 403);
        }

        $photosPaths = [];
        if ($req->hasFile('photos')) {
            $files = array_slice($req->file('photos'), 0, 5); // Max 5 photos
            foreach ($files as $file) {
                $path = $file->store("reviews/{$product->id}", 'public');
                $photosPaths[] = $path;
            }
        }

        $review = $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => $req->integer('rating'),
            'comment' => $req->input('comment'),
            'photos' => $photosPaths ?: null,
            'verified' => true,
            'anonymous' => $req->boolean('anonymous'),
            'status' => 'published',
        ]);

        $summaryService->invalidate($product->id);

        return (new ReviewResource($review->load(['user', 'votes', 'reports'])))->response()->setStatusCode(201);
    }

    public function voteHelpful(Request $req, Review $review)
    {
        $user = $req->user();
        if ($review->user_id === $user->id) {
            return response()->json(['success' => false, 'message' => 'Impossible de voter son propre avis.'], 403);
        }

        $exists = DB::table('review_votes')
            ->where('review_id', $review->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => true, 'alreadyVoted' => true, 'helpfulCount' => $review->helpful_count]);
        }

        DB::transaction(function () use ($review, $user) {
            DB::table('review_votes')->insert([
                'review_id' => $review->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $review->increment('helpful_count');
        });

        return response()->json(['success' => true, 'helpfulCount' => $review->fresh()->helpful_count]);
    }

    public function report(Request $req, Review $review)
    {
        $user = $req->user();
        $reason = $req->input('reason');

        $exists = DB::table('review_reports')
            ->where('review_id', $review->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => true, 'alreadyReported' => true]);
        }

        DB::transaction(function () use ($review, $user, $reason) {
            DB::table('review_reports')->insert([
                'review_id' => $review->id,
                'user_id' => $user->id,
                'reason' => $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $review->increment('reported_count');
        });

        return response()->json(['success' => true]);
    }
}
