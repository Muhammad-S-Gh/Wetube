<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Video;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Str;

class CloudinaryMediaService
{
    public function cloudName(): string
    {
        return (string) config('cloudinary.cloud_name');
    }

    public function videoUrl(string $publicId, int $height, string $format = 'mp4'): string
    {
        $cloud = $this->cloudName();
        $transform = "c_scale,h_{$height},q_auto,f_{$format}";

        return "https://res.cloudinary.com/{$cloud}/video/upload/{$transform}/{$publicId}.{$format}";
    }

    public function thumbnailUrl(string $publicId, ?int $width = null, ?int $height = null): string
    {
        $cloud = $this->cloudName();
        $width ??= (int) config('cloudinary.thumbnail.width', 320);
        $height ??= (int) config('cloudinary.thumbnail.height', 180);
        $transform = "c_fill,w_{$width},h_{$height},q_auto";

        return "https://res.cloudinary.com/{$cloud}/image/upload/{$transform}/{$publicId}";
    }

    /**
     * @return array<string, list<array{path: string, quality: string, qualityValue: int}>>
     */
    public function buildVariantMap(Video $video): array
    {
        $source = $video->media->firstWhere('resource_type', 'video');

        if (!$source) {
            return [];
        }

        $sourceHeight = (int) ($source->quality ?: $this->extractHeightFromCloudResponse($source));

        if ($sourceHeight <= 0) {
            $sourceHeight = 720;
        }

        $resolutions = $this->availableResolutions($sourceHeight);
        $variantMap = [];

        foreach (config('cloudinary.video_formats', ['mp4', 'webm']) as $format) {
            foreach ($resolutions as $height) {
                $variantMap[$format][] = [
                    'path' => $this->videoUrl($source->public_id, $height, $format),
                    'quality' => $height . 'p',
                    'qualityValue' => $height,
                ];
            }

            usort(
                $variantMap[$format],
                fn(array $a, array $b) => $b['qualityValue'] <=> $a['qualityValue']
            );
        }

        return $variantMap;
    }

    public function bestPlaybackUrl(Video $video, string $format = 'mp4'): ?string
    {
        $source = $video->media->firstWhere('resource_type', 'video');

        if (!$source) {
            return null;
        }

        $sourceHeight = (int) ($source->quality ?: $this->extractHeightFromCloudResponse($source));

        if ($sourceHeight <= 0) {
            $sourceHeight = 720;
        }

        return $this->videoUrl($source->public_id, $sourceHeight, $format);
    }

    public function imageDisplayUrl(?Media $image): ?string
    {
        if (!$image || $image->resource_type !== 'image') {
            return null;
        }

        return $this->thumbnailUrl($image->public_id);
    }

    /**
     * Stream a file from disk to Cloudinary (used by queue workers after moderation).
     *
     * @return array<string, mixed>
     */
    public function uploadVideoFromPath(string $absolutePath, int $videoId): array
    {
        return $this->apiResponseToArray((new UploadApi())->upload($absolutePath, [
            'resource_type' => 'video',
            'folder' => config('filesystems.disks.cloudinary.videos'),
            'public_id' => $this->buildPublicId($videoId, 'video'),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function uploadImageFromPath(string $absolutePath, int $videoId): array
    {
        $thumb = config('cloudinary.thumbnail');

        return $this->apiResponseToArray((new UploadApi())->upload($absolutePath, [
            'resource_type' => 'image',
            'folder' => config('filesystems.disks.cloudinary.images'),
            'public_id' => $this->buildPublicId($videoId, 'thumb'),
            'transformation' => [[
                'width' => $thumb['width'],
                'height' => $thumb['height'],
                'crop' => 'fill',
                'quality' => 'auto',
            ]],
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeVideoPayload(array $cloudinaryPayload): array
    {
        return [
            'public_id' => (string) $cloudinaryPayload['public_id'],
            'secure_url' => (string) $cloudinaryPayload['secure_url'],
            'resource_type' => (string) ($cloudinaryPayload['resource_type'] ?? 'video'),
            'format' => (string) ($cloudinaryPayload['format'] ?? 'mp4'),
            'width' => (int) ($cloudinaryPayload['width'] ?? 0),
            'height' => (int) ($cloudinaryPayload['height'] ?? 0),
            'duration' => (float) ($cloudinaryPayload['duration'] ?? 0),
            'raw' => $cloudinaryPayload,
        ];
    }

    /**
     * @param  array<string, mixed>  $cloudinaryPayload
     * @return array<string, mixed>
     */
    public function normalizeImagePayload(array $cloudinaryPayload): array
    {
        return [
            'public_id' => (string) $cloudinaryPayload['public_id'],
            'secure_url' => (string) $cloudinaryPayload['secure_url'],
            'resource_type' => (string) ($cloudinaryPayload['resource_type'] ?? 'image'),
            'format' => (string) ($cloudinaryPayload['format'] ?? 'jpg'),
            'width' => (int) ($cloudinaryPayload['width'] ?? 0),
            'height' => (int) ($cloudinaryPayload['height'] ?? 0),
            'raw' => $cloudinaryPayload,
        ];
    }

    /**
     * @return list<int>
     */
    public function availableResolutions(int $sourceHeight): array
    {
        $configured = config('cloudinary.video_resolutions', [1080, 720, 480, 360, 240]);

        $resolutions = array_values(array_filter(
            $configured,
            fn(int $height) => $height <= $sourceHeight
        ));

        if (empty($resolutions)) {
            $resolutions = [$sourceHeight];
        }

        if (!in_array($sourceHeight, $resolutions, true)) {
            $resolutions[] = $sourceHeight;
            rsort($resolutions);
        }

        return $resolutions;
    }

    protected function buildPublicId(int $videoId, string $suffix): string
    {
        return "optimized/{$videoId}/" . Str::uuid() . "_{$suffix}";
    }

    protected function extractHeightFromCloudResponse(Media $media): int
    {
        if (!$media->cloud_response) {
            return 0;
        }

        $response = json_decode($media->cloud_response, true);

        return (int) ($response['height'] ?? 0);
    }

    /**
     * @return array<string, mixed>
     */
    protected function apiResponseToArray(ApiResponse|array $response): array
    {
        return $response instanceof ApiResponse
            ? $response->getArrayCopy()
            : $response;
    }
}
