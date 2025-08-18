<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ImageClassifierService;

class ImageClassifierController extends Controller
{
    protected ImageClassifierService $classifier;

    public function __construct(ImageClassifierService $classifier)
    {
        $this->classifier = $classifier;
    }

    public function health()
    {
        $result = $this->classifier->health();
        return response()->json($result, $result['ok'] ? 200 : 503);
    }

    public function labels()
    {
        try {
            return response()->json($this->classifier->labels(), 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }
    }

    public function predict(Request $request)
    {
        try {
            /** @var \Illuminate\Http\UploadedFile|null $file */
            $file = $request->file('file');
            $imageUrl = $request->input('image_url');
            $topK = (int) ($request->input('top_k', 5));

            $result = $this->classifier->predict($file, $imageUrl, $topK);
            return response()->json($result, 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }
}


