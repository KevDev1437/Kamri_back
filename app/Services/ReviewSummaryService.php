<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;

class ReviewSummaryService
{
    public function getSummary(int $productId): array
    {
        $key = "product:{$productId}:reviews_summary";
        return Cache::remember($key, 300, function () use ($productId) {
            $base = Review::where('product_id', $productId)
                ->where('status', 'published');

            $total = (clone $base)->count();
            $average = $total ? round((clone $base)->avg('rating'), 1) : 0.0;

            $counts = [];
            foreach ([5,4,3,2,1] as $star) {
                $counts[(string)$star] = (clone $base)->where('rating', $star)->count();
            }

            $withPhotosCount = (clone $base)
                ->whereNotNull('photos')->whereJsonLength('photos', '>', 0)->count();

            return [
                'total' => $total,
                'average' => $average,
                'counts' => $counts,
                'withPhotosCount' => $withPhotosCount,
            ];
        });
    }

    public function invalidate(int $productId): void
    {
        Cache::forget("product:{$productId}:reviews_summary");
    }
}
