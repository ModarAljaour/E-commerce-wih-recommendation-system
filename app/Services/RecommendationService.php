<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecommendationService
{
    protected $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = env('FASTAPI_URL', 'http://127.0.0.1:8002');
    }

    public function getRecommendations(?int $userId = null, ?string $productName = null, int $k = 5)
    {
        $response = Http::get("{$this->apiBaseUrl}/recommend", [
            'user_id' => $userId,
            'product_name' => $productName,
            'k' => $k
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }
}
