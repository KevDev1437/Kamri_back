<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:20', 'max:2000'],
            'anonymous' => ['boolean'],
            'photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'], // 3MB max
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'La note est obligatoire.',
            'rating.min' => 'La note doit être entre 1 et 5 étoiles.',
            'rating.max' => 'La note doit être entre 1 et 5 étoiles.',
            'comment.required' => 'Le commentaire est obligatoire.',
            'comment.min' => 'Le commentaire doit contenir au moins 20 caractères.',
            'comment.max' => 'Le commentaire ne peut pas dépasser 2000 caractères.',
            'photos.*.image' => 'Les fichiers doivent être des images.',
            'photos.*.mimes' => 'Les images doivent être au format JPG, PNG ou WebP.',
            'photos.*.max' => 'Chaque image ne peut pas dépasser 3MB.',
        ];
    }
}
