<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'productId' => $this->product_id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'createdAt' => $this->created_at?->toIso8601String(),
            'verified' => (bool) $this->verified,
            'photos' => $this->getPhotosUrls(),
            'helpfulCount' => (int) $this->helpful_count,
            'reported' => $user ? $this->reports()->where('user_id', $user->id)->exists() : false,
            'helpfulVoted' => $user ? $this->votes()->where('user_id', $user->id)->exists() : false,
            'user' => [
                'name' => $this->anonymous ? 'Acheteur vérifié' : $this->user->name,
                'initials' => $this->anonymous ? 'AV' : substr($this->user->name, 0, 2),
                'isAnonymous' => (bool) $this->anonymous,
            ],
        ];
    }

    private function getPhotosUrls(): array
    {
        if (!$this->photos || !is_array($this->photos)) {
            return [];
        }

        return array_map(function ($path) {
            return Storage::disk('public')->url($path);
        }, $this->photos);
    }
}
