<?php

namespace App\Services;

use App\Models\Resource;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class ImageProcessingService
{
    public function extractExif(UploadedFile $file): array
    {
        $exifData = [];

        try {
            $data = exif_read_data($file->getPathname());
            
            if ($data) {
                $exifData = [
                    'make' => $data['Make'] ?? null,
                    'model' => $data['Model'] ?? null,
                    'lens_model' => $data['LensModel'] ?? null,
                    'focal_length' => $this->parseFocalLength($data['FocalLength'] ?? null),
                    'aperture' => $this->parseAperture($data['FNumber'] ?? null),
                    'shutter_speed' => $data['ExposureTime'] ?? null,
                    'iso' => $data['ISOSpeedRatings'] ?? null,
                    'date_taken' => $this->parseDateTime($data['DateTimeOriginal'] ?? null),
                    'gps_latitude' => $this->parseGPS($data['GPSLatitude'] ?? null, $data['GPSLatitudeRef'] ?? null),
                    'gps_longitude' => $this->parseGPS($data['GPSLongitude'] ?? null, $data['GPSLongitudeRef'] ?? null),
                    'width' => $data['COMPUTED']['Width'] ?? null,
                    'height' => $data['COMPUTED']['Height'] ?? null,
                ];
                
                // Remove null values
                $exifData = array_filter($exifData, fn($value) => $value !== null);
            }
        } catch (\Exception $e) {
            // EXIF extraction failed, return empty array
        }

        return $exifData;
    }

    public function getDimensions(UploadedFile $file): array
    {
        try {
            $image = Image::make($file->getPathname());
            return [
                'width' => $image->width(),
                'height' => $image->height(),
            ];
        } catch (\Exception $e) {
            return ['width' => null, 'height' => null];
        }
    }

    public function processImageAsync(Resource $resource): void
    {
        // This would normally be queued for background processing
        // For now, we'll process synchronously
        $this->processImage($resource);
    }

    public function processImage(Resource $resource): void
    {
        try {
            // Download original from OSS
            $originalPath = $this->downloadFromOSS($resource->oss_key);
            
            $sizes = [
                'thumbnail' => 400,
                'display' => 1280,
                'hd' => 2560,
            ];

            $processedUrls = ['original' => $this->getPublicUrl($resource->oss_key)];

            foreach ($sizes as $name => $maxSize) {
                $processedKey = $this->generateProcessedImage($originalPath, $resource->oss_key, $name, $maxSize);
                $processedUrls[$name] = $this->getPublicUrl($processedKey);
            }

            // Update resource with processed URLs
            $resource->update([
                'processed_sizes' => $processedUrls,
                'processing_complete' => true,
            ]);

            // Clean up temporary file
            unlink($originalPath);

        } catch (\Exception $e) {
            \Log::error('Image processing failed for resource ' . $resource->id . ': ' . $e->getMessage());
        }
    }

    protected function parseFocalLength(?string $value): ?string
    {
        if (!$value) return null;
        
        if (strpos($value, '/') !== false) {
            [$numerator, $denominator] = explode('/', $value);
            return (float)$numerator / (float)$denominator . 'mm';
        }
        
        return $value;
    }

    protected function parseAperture(?string $value): ?string
    {
        if (!$value) return null;
        
        if (strpos($value, '/') !== false) {
            [$numerator, $denominator] = explode('/', $value);
            return 'f/' . ((float)$numerator / (float)$denominator);
        }
        
        return $value;
    }

    protected function parseDateTime(?string $value): ?string
    {
        if (!$value) return null;
        
        try {
            return \Carbon\Carbon::createFromFormat('Y:m:d H:i:s', $value)->toISOString();
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseGPS(?array $coordinates, ?string $ref): ?float
    {
        if (!$coordinates || !$ref) return null;
        
        try {
            $degrees = $coordinates[0];
            $minutes = $coordinates[1];
            $seconds = $coordinates[2];
            
            if (strpos($degrees, '/') !== false) {
                [$num, $den] = explode('/', $degrees);
                $degrees = $num / $den;
            }
            
            if (strpos($minutes, '/') !== false) {
                [$num, $den] = explode('/', $minutes);
                $minutes = $num / $den;
            }
            
            if (strpos($seconds, '/') !== false) {
                [$num, $den] = explode('/', $seconds);
                $seconds = $num / $den;
            }
            
            $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
            
            if (in_array($ref, ['S', 'W'])) {
                $decimal = -$decimal;
            }
            
            return $decimal;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function downloadFromOSS(string $ossKey): string
    {
        // This would download from OSS to a temporary file
        // For now, return a placeholder path
        return '/tmp/' . basename($ossKey);
    }

    protected function generateProcessedImage(string $originalPath, string $originalKey, string $sizeName, int $maxSize): string
    {
        // This would create resized versions and upload to OSS
        // Return the new OSS key
        $pathInfo = pathinfo($originalKey);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $sizeName . '.' . $pathInfo['extension'];
    }

    protected function getPublicUrl(string $ossKey): string
    {
        return app(ResourceService::class)->getPublicUrl($ossKey);
    }
}