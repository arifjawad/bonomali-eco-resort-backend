<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    /**
     * Lightweight check the frontend can call to confirm the API is reachable.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'name' => config('app.name'),
            'api' => 'v1',
            'status' => 'ok',
        ]);
    }
}
