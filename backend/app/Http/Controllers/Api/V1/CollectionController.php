<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:50',
        ]);
        
        $query = Collection::with(['user', 'coverResource']);
        
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
            
            // Filter by visibility
            if (!$user || $user->id !== (int)$request->user_id) {
                $query->public();
            }
        } else {
            $query->public();
        }
        
        $collections = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return response()->json($collections);
    }
    
    public function store(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cover_resource_id' => 'nullable|exists:resources,id',
            'is_public' => 'boolean',
        ]);
        
        // Verify cover resource belongs to user
        if ($request->cover_resource_id) {
            $resource = $user->resources()->find($request->cover_resource_id);
            if (!$resource) {
                return response()->json(['message' => 'Cover resource not found'], 404);
            }
        }
        
        $collection = Collection::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'cover_resource_id' => $request->cover_resource_id,
            'is_public' => $request->is_public ?? true,
        ]);
        
        return response()->json([
            'collection' => $collection->load(['user', 'coverResource']),
            'message' => 'Collection created successfully'
        ], 201);
    }
    
    public function show(Collection $collection, Request $request)
    {
        $user = $request->user();
        
        if (!$collection->canBeViewedBy($user)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        $collection->load(['user', 'coverResource']);
        
        // Get works in collection
        $works = $collection->works()
            ->with(['user', 'coverResource', 'resources'])
            ->get();
        
        // Filter works based on user visibility
        $works = $works->filter(function ($work) use ($user) {
            return $work->canBeViewedBy($user);
        });
        
        return response()->json([
            'collection' => $collection,
            'works' => $works->values(),
        ]);
    }
    
    public function update(Request $request, Collection $collection)
    {
        $user = $request->user();
        
        if ($collection->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'cover_resource_id' => 'sometimes|nullable|exists:resources,id',
            'is_public' => 'sometimes|boolean',
        ]);
        
        // Verify cover resource belongs to user
        if ($request->has('cover_resource_id') && $request->cover_resource_id) {
            $resource = $user->resources()->find($request->cover_resource_id);
            if (!$resource) {
                return response()->json(['message' => 'Cover resource not found'], 404);
            }
        }
        
        $collection->update($request->only([
            'title', 'description', 'cover_resource_id', 'is_public'
        ]));
        
        return response()->json([
            'collection' => $collection->load(['user', 'coverResource']),
            'message' => 'Collection updated successfully'
        ]);
    }
    
    public function destroy(Collection $collection, Request $request)
    {
        $user = $request->user();
        
        if ($collection->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        $collection->delete();
        
        return response()->json(['message' => 'Collection deleted successfully']);
    }
    
    public function addWork(Collection $collection, Request $request)
    {
        $user = $request->user();
        
        if ($collection->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        $request->validate([
            'work_ids' => 'required|array',
            'work_ids.*' => 'exists:works,id',
        ]);
        
        $works = Work::whereIn('id', $request->work_ids)
            ->published()
            ->get();
        
        // Verify user can access all works
        foreach ($works as $work) {
            if (!$work->canBeViewedBy($user)) {
                return response()->json(['message' => 'Cannot access work: ' . $work->id], 403);
            }
        }
        
        DB::beginTransaction();
        
        try {
            $maxSortOrder = $collection->works()->max('pivot_sort_order') ?? 0;
            
            foreach ($works as $index => $work) {
                $collection->works()->syncWithoutDetaching([
                    $work->id => ['sort_order' => $maxSortOrder + $index + 1]
                ]);
            }
            
            DB::commit();
            
            return response()->json(['message' => 'Works added to collection successfully']);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function removeWork(Collection $collection, Work $work, Request $request)
    {
        $user = $request->user();
        
        if ($collection->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        $collection->works()->detach($work->id);
        
        return response()->json(['message' => 'Work removed from collection successfully']);
    }
}