<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Throwable;

class ModerationService
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private float $textToxicThreshold = 0.85,
        private float $textThreatThreshold = 0.85,
        private float $textIdentityHateThreshold = 0.85,
        private float $textObsceneThreshold = 0.85,
        private float $textInsultThreshold = 0.85,
        private float $imageNsfwThreshold = 0.35
    ) {}
    /**
     * Text moderation.
     */
    public function moderateText(string $text): array
    {
        if (!$this->apiKey) {
            return ['pass' => false, 'reason' => 'missing_api_key'];
        }

        $text = trim($text);

        if ($text === '') {
            return ['pass' => true, 'reason' => 'empty'];
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withToken($this->apiKey)
                ->timeout(8)
                ->retry(3, 200, throw: false)
                ->post(
                    $this->baseUrl . '/unitary/toxic-bert',
                    ['inputs' => $text]
                );

            if (!$response->successful()) {
                return ['pass' => false, 'reason' => 'api_error_' . $response->status()];
            }

            $data = $response->json();

            if (!is_array($data)) {
                return ['pass' => false, 'reason' => 'bad_response'];
            }

            if (!empty($data['error'])) {
                Log::warning('HF text moderation error', ['data' => $data]);
                return ['pass' => false, 'reason' => 'model_error:' . $data['error']];
            }

            $thresholdMap = [
                'toxic' => ['threshold' => $this->textToxicThreshold, 'reason' => 'toxic_text'],
                'threat' => ['threshold' => $this->textThreatThreshold, 'reason' => 'threat_text'],
                'identity_hate' => ['threshold' => $this->textIdentityHateThreshold, 'reason' => 'identity_hate_text'],
                'obscene' => ['threshold' => $this->textObsceneThreshold, 'reason' => 'obscene_text'],
                'insult' => ['threshold' => $this->textInsultThreshold, 'reason' => 'insult_text'],
            ];

            foreach ($data[0] ?? [] as $result) {
                if (!is_array($result)) {
                    continue;
                }

                $label = strtolower((string) ($result['label'] ?? ''));
                $score = (float) ($result['score'] ?? 0.0);

                if (!isset($thresholdMap[$label])) {
                    continue;
                }

                $threshold = (float) $thresholdMap[$label]['threshold'];
                $reason = (string) $thresholdMap[$label]['reason'];

                if ($score >= $threshold) {
                    return [
                        'pass' => false,
                        'reason' => $reason . ':' . number_format($score, 3, '.', '')
                    ];
                }
            }

            return ['pass' => true, 'reason' => 'clean'];
        } catch (Throwable $e) {
            return ['pass' => false, 'reason' => $e->getMessage()];
        }
    }

    /**
     * Image moderation.
     */
    public function moderateImage(string $filePath)
    {
        if (!$this->apiKey) {
            return ['pass' => false, 'reason' => 'missing_api_key'];
        }

        $fullPath = Storage::disk('uploads_tmp')->path($filePath);

        Log::info('Moderation path check', [
            'input' => $filePath,
            'resolved' => $fullPath,
            'exists' => file_exists($fullPath),
        ]);

        if (!is_file($fullPath)) {
            return ['pass' => false, 'reason' => 'file_not_found'];
        }

        $binary = file_get_contents($fullPath);

        if (!$binary) {
            return ['pass' => false, 'reason' => 'file_read_error'];
        }

        return $this->moderateImageBinary($binary);
    }

    /**
     * Video moderation.
     */
    public function moderateVideo(string $videoPath): array
    {
        $fullPath = Storage::disk('uploads_tmp')->path($videoPath);

        if (!is_file($fullPath)) {
            return ['pass' => false, 'reason' => 'file_not_found'];
        }

        try {
            $media = FFMpeg::fromDisk('uploads_tmp')->open($videoPath);

            $duration = max(1, (int) $media->getDurationInSeconds());

            $middle = (int) floor($duration / 2);
            $start = (int) floor($duration * 5) / 100;
            $end = (int) floor($duration * 95) / 100;

            $frames = [$start, $middle, $end];

            foreach ($frames as $frame) {
                $media = FFMpeg::fromDisk('uploads_tmp')->open($videoPath);
                $frameBinary = $media
                    ->getFrameFromSeconds((float) $frame)
                    ->export()
                    ->getFrameContents();

                if (!$frameBinary) {
                    return ['pass' => false, 'reason' => 'frame_error'];
                }

                $result = $this->moderateImageBinary($frameBinary);

                if (!$result['pass']) {
                    return $result;
                }
            }

            return ['pass' => true, 'reason' => 'clean'];
        } catch (Throwable $e) {
            Log::error('Video moderation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['pass' => false, 'reason' => $e->getMessage()];
        }
    }

    private function moderateImageBinary(string $binary): array
    {
        if (!$this->apiKey) {
            return ['pass' => false, 'reason' => 'missing_api_key'];
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withToken($this->apiKey)
                ->timeout(120)
                ->connectTimeout(10)
                ->withBody($binary, 'application/octet-stream')
                ->retry(3, 200, throw: false)
                ->post($this->baseUrl . '/Falconsai/nsfw_image_detection');

            if (!$response->successful()) {
                return ['pass' => false, 'reason' => 'api_error_' . $response->status()];
            }

            $data = $response->json();

            if (!is_array($data)) {
                return ['pass' => false, 'reason' => 'bad_response'];
            }

            if (!empty($data['error'])) {
                Log::warning('HF image moderation error response', ['data' => $data]);
                return ['pass' => false, 'reason' => 'model_loading:' . $data['error']];
            }

            $results = $data;
            if (isset($data[0]) && isset($data[0][0])) {
                $results = $data[0];
            }

            foreach ($results as $result) {
                if (!is_array($result)) {
                    continue;
                }

                if (strtolower((string) ($result['label'] ?? '')) === 'nsfw' && (float) ($result['score'] ?? 0.0) > $this->imageNsfwThreshold) {
                    return ['pass' => false, 'reason' => 'nsfw_frame'];
                }
            }

            return ['pass' => true, 'reason' => 'clean'];
        } catch (Throwable $e) {
            Log::error('Image moderation exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return ['pass' => false, 'reason' => $e->getMessage()];
        }
    }
}
