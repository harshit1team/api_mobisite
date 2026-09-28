<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * List all blogs for admin.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Blog::query()->orderBy('created_at', 'desc');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $blogs = $query->get();

        return response()->json([
            'success' => true,
            'blogs' => $blogs,
        ]);
    }

    /**
     * Store a newly created blog.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
            'focus_keyword' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
            'author_name' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Blog::generateUniqueSlug($validated['title']);

        // Check if slug taken
        if (Blog::where('slug', $slug)->exists()) {
            $slug = Blog::generateUniqueSlug($slug);
        }

        $user = $request->user();
        $authorName = $validated['author_name'] ?? ($user ? $user->name : 'Admin');
        $authorId = $user ? $user->id : null;

        $publishedAt = null;
        if ($validated['status'] === 'published') {
            $publishedAt = !empty($validated['published_at']) ? $validated['published_at'] : now();
        } elseif ($validated['status'] === 'scheduled') {
            $publishedAt = !empty($validated['published_at']) ? $validated['published_at'] : now()->addDay();
        }

        $blog = Blog::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'featured_image' => $validated['featured_image'] ?? null,
            'author_id' => $authorId,
            'author_name' => $authorName,
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
            'focus_keyword' => $validated['focus_keyword'] ?? null,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Blog post created successfully.',
            'blog' => $blog,
        ], 201);
    }

    /**
     * Show a specific blog.
     */
    public function show(Blog $blog): JsonResponse
    {
        return response()->json([
            'success' => true,
            'blog' => $blog,
        ]);
    }

    /**
     * Update an existing blog.
     */
    public function update(Request $request, Blog $blog): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
            'focus_keyword' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
            'author_name' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Blog::generateUniqueSlug($validated['title'], $blog->id);

        if (Blog::where('slug', $slug)->where('id', '!=', $blog->id)->exists()) {
            $slug = Blog::generateUniqueSlug($slug, $blog->id);
        }

        $publishedAt = $blog->published_at;
        if ($validated['status'] === 'published') {
            $publishedAt = !empty($validated['published_at']) ? $validated['published_at'] : ($blog->published_at ?? now());
        } elseif ($validated['status'] === 'scheduled') {
            $publishedAt = !empty($validated['published_at']) ? $validated['published_at'] : ($blog->published_at ?? now()->addDay());
        } elseif ($validated['status'] === 'draft') {
            $publishedAt = null;
        }

        $blog->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'featured_image' => $validated['featured_image'] ?? null,
            'author_name' => $validated['author_name'] ?? $blog->author_name,
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
            'focus_keyword' => $validated['focus_keyword'] ?? null,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Blog updated successfully.',
            'blog' => $blog->fresh(),
        ]);
    }

    /**
     * Delete a blog.
     */
    public function destroy(Blog $blog): JsonResponse
    {
        $blog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog deleted successfully.',
        ]);
    }

    /**
     * Upload an image (used by TinyMCE or Featured Image uploader).
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'], // max 5MB
        ]);

        $path = $request->file('image')->store('blogs', 'public');
        $url = asset('storage/' . $path);

        return response()->json([
            'success' => true,
            'url' => $url,
            'location' => $url, // TinyMCE standard upload response expects 'location'
        ]);
    }
}
