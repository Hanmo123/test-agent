<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Draft;
use Illuminate\Http\Request;

class DraftController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:50',
        ]);
        
        $drafts = $user->drafts()
            ->with(['coverResource'])
            ->orderBy('updated_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return response()->json($drafts);
    }
    
    public function store(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content_json' => 'nullable|array',
            'cover_resource_id' => 'nullable|exists:resources,id',
            'tags' => 'nullable|array|max:' . config('onlyshots.limits.max_tags_per_work'),
            'tags.*' => 'string|max:50',
        ]);
        
        // Verify cover resource belongs to user
        if ($request->cover_resource_id) {
            $resource = $user->resources()->find($request->cover_resource_id);
            if (!$resource) {
                return response()->json(['message' => 'Cover resource not found'], 404);
            }
        }
        
        $draft = Draft::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'content_json' => $request->content_json ?? [],
            'cover_resource_id' => $request->cover_resource_id,
            'tags' => $request->tags,
        ]);
        
        return response()->json([
            'draft' => $draft->load('coverResource'),
            'message' => 'Draft created successfully'
        ], 201);
    }
    
    public function show(Draft $draft, Request $request)
    {
        $user = $request->user();
        
        if ($draft->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        return response()->json([
            'draft' => $draft->load(['coverResource'])
        ]);
    }
    
    public function update(Request $request, Draft $draft)
    {
        $user = $request->user();
        
        if ($draft->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        $request->validate([
            'title' => 'sometimes|nullable|string|max:255',
            'content_json' => 'sometimes|nullable|array',
            'cover_resource_id' => 'sometimes|nullable|exists:resources,id',
            'tags' => 'sometimes|nullable|array|max:' . config('onlyshots.limits.max_tags_per_work'),
            'tags.*' => 'string|max:50',
        ]);
        
        // Verify cover resource belongs to user
        if ($request->has('cover_resource_id') && $request->cover_resource_id) {
            $resource = $user->resources()->find($request->cover_resource_id);
            if (!$resource) {
                return response()->json(['message' => 'Cover resource not found'], 404);
            }
        }
        
        // Save to history before updating
        if ($request->has('content_json')) {
            $draft->addToHistory($draft->content_json ?? []);
        }
        
        $draft->update($request->only([
            'title', 'content_json', 'cover_resource_id', 'tags'
        ]));
        
        return response()->json([
            'draft' => $draft->load('coverResource'),
            'message' => 'Draft updated successfully'
        ]);
    }
    
    public function destroy(Draft $draft, Request $request)
    {
        $user = $request->user();
        
        if ($draft->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        // Check if draft has published work
        if ($draft->work && $draft->work->isPublished()) {
            return response()->json(['message' => 'Cannot delete draft with published work'], 400);
        }
        
        $draft->delete();
        
        return response()->json(['message' => 'Draft deleted successfully']);
    }
}