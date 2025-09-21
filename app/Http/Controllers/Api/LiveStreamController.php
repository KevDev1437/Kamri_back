<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LiveStreamController extends Controller
{
    /**
     * Afficher le stream en direct actuel
     */
    public function index(): JsonResponse
    {
        try {
            $liveStream = LiveStream::live()->first();

            if (!$liveStream) {
                // Retourner un stream par défaut si aucun n'est actif
                return response()->json([
                    'url' => 'https://via.placeholder.com/800x450',
                    'title' => 'Aucun stream en direct',
                    'is_live' => false
                ]);
            }

            return response()->json([
                'url' => $liveStream->stream_url,
                'title' => $liveStream->title,
                'description' => $liveStream->description,
                'thumbnail' => $liveStream->thumbnail,
                'viewer_count' => $liveStream->viewer_count,
                'is_live' => true,
                'started_at' => $liveStream->started_at
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du stream',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher tous les streams programmés
     */
    public function scheduled(): JsonResponse
    {
        try {
            $streams = LiveStream::where('scheduled_at', '>', now())
                ->orderBy('scheduled_at')
                ->get();

            return response()->json($streams);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des streams programmés',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
