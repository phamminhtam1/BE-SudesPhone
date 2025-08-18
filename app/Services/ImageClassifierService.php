<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class ImageClassifierService
{
    protected string $baseUrl;
    protected int $timeoutSeconds;

    public function __construct()
    {
        $this->baseUrl = rtrim(Config::get('services.image_classifier.base_url'), '/');
        $this->timeoutSeconds = (int) Config::get('services.image_classifier.timeout', 30);
    }

    public function health(): array
    {
        $response = Http::timeout($this->timeoutSeconds)
            ->get($this->baseUrl . '/health');

        return [
            'ok' => $response->successful(),
            'data' => $response->json(),
            'status' => $response->status(),
        ];
    }

    public function labels(): array
    {
        $response = Http::timeout($this->timeoutSeconds)
            ->get($this->baseUrl . '/labels');

        if (!$response->successful()) {
            throw new \RuntimeException('Classifier labels error: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Predict from either uploaded file or image URL.
     * Provide exactly one of $file or $imageUrl.
     */
    public function predict(?UploadedFile $file = null, ?string $imageUrl = null, int $topK = 5): array
    {
        if (($file === null) === ($imageUrl === null)) {
            throw new \InvalidArgumentException('Provide exactly one of $file or $imageUrl');
        }

        $req = Http::timeout($this->timeoutSeconds)->acceptJson();

        if ($file !== null) {
            $req = $req->attach(
                'file',
                fopen($file->getRealPath(), 'r'),
                $file->getClientOriginalName()
            );
        } else {
            $req = $req->asMultipart();
        }

        $payload = ['top_k' => (string) $topK];
        if ($imageUrl !== null) {
            $payload['image_url'] = $imageUrl;
        }

        $response = $req->post($this->baseUrl . '/predict', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Classifier predict error: ' . $response->body());
        }

        return $response->json();
    }
}


