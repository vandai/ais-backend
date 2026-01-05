<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * List all categories.
     */
    public function index(): JsonResponse
    {
        $categories = PostCategory::orderBy('name', 'asc')->get();

        return response()->json([
            'data' => $categories,
        ]);
    }
}
