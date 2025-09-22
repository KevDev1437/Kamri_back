<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'rating'       => (int) $this->rating,
            'comment'      => $this->comment,
            'anonymous'    => (bool) $this->anonymous,
            'verified'     => (bool) $this->verified,
            'helpfulCount' => (int) $this->helpful_count,
            'createdAt'    => $this->created_at?->toIso8601String(),

            'user' => $this->when(!$this->anonymous, function () {
                return [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'avatar'=> $this->user->avatar ?? null,
                ];
            }, null),

            'photos' => $this->photos->map(fn($p) => $p->path)->values(),
        ];
    }
}
