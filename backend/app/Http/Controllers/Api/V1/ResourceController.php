<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\Draft;
use App\Services\ResourceService;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ResourceController extends Controller
{
    protected $resourceService;
    protected $imageProcessingService;

    public function __construct(ResourceService $resourceService, ImageProcessingService $imageProcessingService)
    {
        $this->resourceService = $resourceService;
        $this->imageProcessingService = $imageProcessingService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'relationship_id' => 'nullable|exists:relationships,id',
            'search' => 'nullable|string|max:255',
            'type' => ['nullable', Rule::in(['image', 'video'])],
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:50',
        ]);

        $query = Resource::where('user_id', $user->id);

        if ($request->relationship_id) {
            $query->where('relationship_id', $request->relationship_id);
        }

        if ($request->search) {
            $query->where('filename', 'like', '%' . $request->search . '%');
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $resources = $query->with(['relationship'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($resources);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required|array|max:' . config('onlyshots.limits.batch_upload_limit'),
            'files.*' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,heif,dng',
                'max:' . (config('onlyshots.limits.max_file_size') / 1024),
            ],
            'relationship_id' => 'required|exists:relationships,id',
        ]);

        $user = $request->user();
        $results = [];

        foreach ($request->file('files') as $file) {
            try {
                // Upload to OSS
                $ossKey = $this->resourceService->uploadToOSS($file);
                
                // Extract EXIF data
                $exifData = $this->imageProcessingService->extractExif($file);
                
                // Get image dimensions
                $dimensions = $this->imageProcessingService->getDimensions($file);
                
                // Create resource record
                $resource = Resource::create([
                    'user_id' => $user->id,
                    'relationship_id' => $request->relationship_id,
                    'filename' => $file->getClientOriginalName(),
                    'oss_key' => $ossKey,
                    'type' => 'image',
                    'exif_json' => $exifData,
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height'],
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'processing_complete' => false,
                ]);

                // Create default draft
                Draft::create([
                    'user_id' => $user->id,
                    'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'content_json' => [
                        'resources' => [$resource->id],
                        'text' => '',
                    ],
                    'cover_resource_id' => $resource->id,
                ]);

                // Queue image processing
                $this->imageProcessingService->processImageAsync($resource);

                $results[] = [
                    'success' => true,
                    'resource' => $resource,
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    public function show(Resource $resource, Request $request)
    {
        $user = $request->user();
        
        if ($resource->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(['resource' => $resource->load('relationship')]);
    }

    public function download(Resource $resource, Request $request)
    {
        $user = $request->user();
        
        if (!$user->canDownloadOriginal()) {
            return response()->json(['message' => 'Level 1+ required for original downloads'], 403);
        }

        // Check daily limit for non-members
        if (!$user->isActiveMember()) {
            $dailyDownloads = $user->resources()
                ->whereDate('created_at', today())
                ->count();
                
            if ($dailyDownloads >= $user->getDailyOriginalDownloadLimit()) {
                return response()->json(['message' => 'Daily download limit reached'], 429);
            }
        }

        // Generate temporary download URL
        $downloadUrl = $this->resourceService->generateDownloadUrl($resource);

        // Log download for audit
        // TODO: Implement audit logging

        return response()->json(['download_url' => $downloadUrl]);
    }

    public function destroy(Resource $resource, Request $request)
    {
        $user = $request->user();
        
        if ($resource->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $resource->delete(); // Soft delete

        return response()->json(['message' => 'Resource deleted successfully']);
    }
}