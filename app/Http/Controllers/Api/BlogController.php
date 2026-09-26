<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * List all published/scheduled blogs that have reached their scheduled time.
     */
    public function index(Request $request): JsonResponse
    {
        $now = now();

        $query = Blog::where(function ($q) use ($now) {
            $q->where('status', 'published')
              ->orWhere(function ($sub) use ($now) {
                  $sub->where('status', 'scheduled')
                      ->whereNotNull('published_at')
                      ->where('published_at', '<=', $now);
              });
        })
        ->where(function ($q) use ($now) {
            // Also for published posts, if published_at is in the future, don't show yet
            $q->whereNull('published_at')
              ->orWhere('published_at', '<=', $now);
        })
        ->orderBy('published_at', 'desc');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $blogs = $query->paginate($request->query('per_page', 10));

        return response()->json([
            'success' => true,
            'blogs' => $blogs,
        ]);
    }

    /**
     * Show a published/scheduled blog by slug or ID.
     */
    public function show(string $slugOrId): JsonResponse
    {
        $now = now();

        $blog = Blog::where(function ($q) use ($now) {
            $q->where('status', 'published')
              ->orWhere(function ($sub) use ($now) {
                  $sub->where('status', 'scheduled')
                      ->whereNotNull('published_at')
                      ->where('published_at', '<=', $now);
              });
        })
        ->where(function ($q) use ($now) {
            $q->whereNull('published_at')
              ->orWhere('published_at', '<=', $now);
        })
        ->where(function ($q) use ($slugOrId) {
            $q->where('slug', $slugOrId)
              ->orWhere('id', $slugOrId);
        })
        ->firstOrFail();

        return response()->json([
            'success' => true,
            'blog' => $blog,
        ]);
    }
}
