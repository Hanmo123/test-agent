<?php

namespace App\Services;

use App\Models\Resource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OSS\OssClient;
use OSS\Core\OssException;

class ResourceService
{
    protected $ossClient;

    public function __construct()
    {
        $this->ossClient = new OssClient(
            config('onlyshots.oss.access_key_id'),
            config('onlyshots.oss.access_key_secret'),
            config('onlyshots.oss.endpoint')
        );
    }

    public function uploadToOSS(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $ossKey = 'uploads/' . date('Y/m/d') . '/' . $filename;

        try {
            $this->ossClient->uploadFile(
                config('onlyshots.oss.bucket'),
                $ossKey,
                $file->getPathname()
            );

            return $ossKey;
        } catch (OssException $e) {
            throw new \Exception('Failed to upload file: ' . $e->getMessage());
        }
    }

    public function generateDownloadUrl(Resource $resource): string
    {
        try {
            // Generate a signed URL valid for 15 minutes
            $signedUrl = $this->ossClient->signUrl(
                config('onlyshots.oss.bucket'),
                $resource->oss_key,
                15 * 60 // 15 minutes
            );

            return $signedUrl;
        } catch (OssException $e) {
            throw new \Exception('Failed to generate download URL: ' . $e->getMessage());
        }
    }

    public function deleteFromOSS(string $ossKey): bool
    {
        try {
            $this->ossClient->deleteObject(
                config('onlyshots.oss.bucket'),
                $ossKey
            );

            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    public function getPublicUrl(string $ossKey): string
    {
        $cdnDomain = config('onlyshots.oss.cdn_domain');
        
        if ($cdnDomain) {
            return "https://{$cdnDomain}/{$ossKey}";
        }

        return "https://" . config('onlyshots.oss.bucket') . "." . config('onlyshots.oss.endpoint') . "/" . $ossKey;
    }
}