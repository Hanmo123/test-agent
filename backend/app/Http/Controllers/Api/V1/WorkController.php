<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Work;
use App\Models\Draft;
use App\Models\Like;
use App\Services\ContentModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkController extends Controller
{
    protected $contentModerationService;

    public function __construct(ContentModerationService $contentModerationService)
    {
        $this->contentModerationService = $contentModerationService;
    }

    public function index(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:50',
        ]);

        $query = Work::published()
            ->with(['user', 'coverResource', 'resources']);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->tags) {
            foreach ($request->tags as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        // Filter based on user visibility
        $currentUser = $request->user();
        if ($currentUser) {
            $query->where(function ($q) use ($currentUser) {
                $q->whereHas('user', function ($userQuery) {
                    $userQuery->where('visibility', 'public');
                })->orWhere('user_id', $currentUser->id)
                  ->orWhereHas('user', function ($userQuery) use ($currentUser) {
                      $userQuery->where('visibility', 'followers_only')
                          ->whereHas('followers', function ($followQuery) use ($currentUser) {
                              $followQuery->where('follower_user_id', $currentUser->id);
                          });
                  });
            });
        } else {
            $query->whereHas('user', function ($userQuery) {
                $userQuery->where('visibility', 'public');
            });
        }

        $works = $query->orderBy('published_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($works);
    }

    public function store(Request $request)
    {
        $request->validate([
            'draft_id' => 'required|exists:drafts,id',
        ]);

        $user = $request->user();
        $draft = Draft::where('id', $request->draft_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if draft already has a published work
        if ($draft->work && $draft->work->isPublished()) {
            return response()->json(['message' => 'Draft already published'], 400);
        }

        DB::beginTransaction();
        
        try {
            // Create or update work
            $work = $draft->work ?: new Work();
            $work->fill([
                'draft_id' => $draft->id,
                'user_id' => $user->id,
                'relationship_id' => $draft->relationship_id ?? $user->relationships()->where('type', 'personal')->first()->id,
                'status' => 'pending_review',
                'title' => $draft->title,
                'content' => $draft->content_json['text'] ?? '',
                'tags' => $draft->tags,
                'cover_resource_id' => $draft->cover_resource_id,
            ]);
            $work->save();

            // Sync resources
            if (isset($draft->content_json['resources'])) {
                $resourceData = [];
                foreach ($draft->content_json['resources'] as $index => $resourceId) {
                    $resourceData[$resourceId] = ['sort_order' => $index];
                }
                $work->resources()->sync($resourceData);
            }

            // Submit for content moderation
            $moderationResult = $this->contentModerationService->moderateContent(
                $work->title . ' ' . $work->content,
                $work->resources->pluck('oss_key')->toArray()
            );

            if ($moderationResult['approved']) {
                $work->status = 'published';
                $work->published_at = now();
            } else {
                $work->status = 'rejected';
                $work->moderation_result = $moderationResult;
            }

            $work->save();

            DB::commit();

            return response()->json([
                'work' => $work->load(['user', 'coverResource', 'resources']),
                'message' => $work->isPublished() ? 'Work published successfully' : 'Work submitted for review',
            ], $work->isPublished() ? 201 : 202);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function show(Work $work, Request $request)
    {
        $user = $request->user();
        
        if (!$work->canBeViewedBy($user)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Increment view count
        $work->increment('views_count');

        $work->load(['user', 'coverResource', 'resources', 'relationship']);

        // Include like status if user is logged in
        if ($user) {
            $work->is_liked = $work->likes()->where('user_id', $user->id)->exists();
        }

        return response()->json(['work' => $work]);
    }

    public function update(Request $request, Work $work)
    {
        $user = $request->user();
        
        if ($work->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'tags' => 'sometimes|array|max:' . config('onlyshots.limits.max_tags_per_work'),
            'tags.*' => 'string|max:50',
        ]);

        $originalContent = $work->title . ' ' . $work->content;
        $work->fill($request->only(['title', 'content', 'tags']));
        
        $newContent = $work->title . ' ' . $work->content;
        
        // Re-moderate if content changed
        if ($originalContent !== $newContent) {
            $moderationResult = $this->contentModerationService->moderateContent($newContent);
            
            if (!$moderationResult['approved']) {
                $work->status = 'rejected';
                $work->moderation_result = $moderationResult;
            }
        }

        $work->save();

        return response()->json([
            'work' => $work->load(['user', 'coverResource', 'resources']),
        ]);
    }

    public function destroy(Work $work, Request $request)
    {
        $user = $request->user();
        
        if ($work->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $work->delete(); // Soft delete

        return response()->json(['message' => 'Work deleted successfully']);
    }

    public function like(Work $work, Request $request)
    {
        $user = $request->user();
        
        if (!$user->canLike()) {
            return response()->json(['message' => 'Level 1+ required to like works'], 403);
        }

        if (!$work->canBeViewedBy($user)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $like = Like::firstOrCreate([
            'user_id' => $user->id,
            'work_id' => $work->id,
        ]);

        if ($like->wasRecentlyCreated) {
            $work->increment('likes_count');
            $work->user->increment('likes_received');
        }

        return response()->json(['message' => 'Work liked successfully']);
    }

    public function unlike(Work $work, Request $request)
    {
        $user = $request->user();
        
        $like = Like::where('user_id', $user->id)
            ->where('work_id', $work->id)
            ->first();

        if ($like) {
            $like->delete();
            $work->decrement('likes_count');
            $work->user->decrement('likes_received');
        }

        return response()->json(['message' => 'Work unliked successfully']);
    }
}