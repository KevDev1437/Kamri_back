<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewHelpfulVote;
use App\Models\ReviewReport;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewActionsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    public function helpful(Request $request, Review $review)
    {
        $user = $request->user();

        // Un seul vote par user/review
        $exists = ReviewHelpfulVote::where('review_id', $review->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'review' => 'Vous avez déjà voté pour cet avis.',
            ]);
        }

        ReviewHelpfulVote::create([
            'review_id' => $review->id,
            'user_id'   => $user->id,
        ]);

        $review->increment('helpful_count');

        return response()->json([
            'success'      => true,
            'helpfulCount' => (int) $review->helpful_count,
            'message'      => 'Merci pour votre vote !',
        ]);
    }

    public function report(Request $request, Review $review)
    {
        $user = $request->user();
        $data = $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        $exists = ReviewReport::where('review_id', $review->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'review' => 'Vous avez déjà signalé cet avis.',
            ]);
        }

        ReviewReport::create([
            'review_id' => $review->id,
            'user_id'   => $user->id,
            'reason'    => $data['reason'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Avis signalé avec succès',
        ]);
    }
}
