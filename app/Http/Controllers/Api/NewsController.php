<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * List all published news with pagination.
     *
     * Sorted by latest created news.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $perPage = $request->input('per_page', 10);

        $news = Post::published()
            ->with(['author:id,name', 'categories:id,name,slug'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $news->items(),
            'meta' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
            ],
            'links' => [
                'first' => $news->url(1),
                'last' => $news->url($news->lastPage()),
                'prev' => $news->previousPageUrl(),
                'next' => $news->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Show news detail by ID.
     */
    public function show(string $newsId): JsonResponse
    {
        // Validate that news_id is a positive integer
        if (!ctype_digit($newsId) || (int) $newsId <= 0) {
            return response()->json([
                'message' => 'Invalid news ID. Must be a positive integer.',
                'errors' => [
                    'news_id' => ['The news ID must be a positive integer.']
                ]
            ], 422);
        }

        $news = Post::published()
            ->with(['author:id,name', 'categories:id,name,slug,description'])
            ->find((int) $newsId);

        if (!$news) {
            return response()->json([
                'message' => 'News not found.',
                'errors' => [
                    'news_id' => ['No news found with the provided ID.']
                ]
            ], 404);
        }

        return response()->json([
            'data' => $news,
        ]);
    }

    /**
     * Search news by keywords, date range, and category.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => ['sometimes', 'nullable', 'string', 'max:255'],
            'start_date' => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:post_categories,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ], [
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
        ]);

        $perPage = $request->input('per_page', 10);

        $news = Post::published()
            ->with(['author:id,name', 'categories:id,name,slug'])
            ->search($request->input('keyword'))
            ->dateRange($request->input('start_date'), $request->input('end_date'))
            ->inCategory($request->input('category_id'))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $news->items(),
            'meta' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
            ],
            'links' => [
                'first' => $news->url(1),
                'last' => $news->url($news->lastPage()),
                'prev' => $news->previousPageUrl(),
                'next' => $news->nextPageUrl(),
            ],
            'filters' => [
                'keyword' => $request->input('keyword'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'category_id' => $request->input('category_id'),
            ],
        ]);
    }
}
