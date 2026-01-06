<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use Illuminate\Http\JsonResponse;

class EventCategoryController extends Controller
{
    /**
     * List all event categories.
     */
    public function index(): JsonResponse
    {
        $categories = EventCategory::withCount(['events' => function ($query) {
            $query->where('status', 'published');
        }])
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }
}
