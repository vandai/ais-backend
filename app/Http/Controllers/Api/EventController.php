<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * List all published events with pagination.
     *
     * Supports filtering by category and keyword search.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:event_categories,id'],
            'keyword' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $perPage = $request->input('per_page', 10);

        $events = Event::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->inCategory($request->input('category_id'))
            ->search($request->input('keyword'))
            ->orderBy('start_datetime', 'asc')
            ->paginate($perPage);

        return response()->json([
            'data' => $events->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
            'links' => [
                'first' => $events->url(1),
                'last' => $events->url($events->lastPage()),
                'prev' => $events->previousPageUrl(),
                'next' => $events->nextPageUrl(),
            ],
            'filters' => [
                'category_id' => $request->input('category_id'),
                'keyword' => $request->input('keyword'),
            ],
        ]);
    }

    /**
     * Show event detail by ID.
     */
    public function show(string $eventId): JsonResponse
    {
        if (!ctype_digit($eventId) || (int) $eventId <= 0) {
            return response()->json([
                'message' => 'Invalid event ID. Must be a positive integer.',
                'errors' => [
                    'event_id' => ['The event ID must be a positive integer.']
                ]
            ], 422);
        }

        $event = Event::published()
            ->with(['author:id,name', 'category:id,name,slug,description'])
            ->find((int) $eventId);

        if (!$event) {
            return response()->json([
                'message' => 'Event not found.',
                'errors' => [
                    'event_id' => ['No event found with the provided ID.']
                ]
            ], 404);
        }

        return response()->json([
            'data' => $event,
        ]);
    }
}
