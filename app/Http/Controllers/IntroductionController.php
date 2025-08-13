<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\IntroductionService;

class IntroductionController extends Controller
{
    protected $introductionService;

    public function __construct(IntroductionService $introductionService)
    {
        $this->introductionService = $introductionService;
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if(!$admin){
            return response()->json(['message' => 'Không xác thực'], 401);
        }
        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 403);
        }

        try {
            $data = $validate->validated();
            $introduction = $this->introductionService->updateIntroduction($data);
            return response()->json([
                'message' => 'success',
                'introduction' => $introduction,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function get()
    {
        try {
            $introduction = $this->introductionService->getIntroduction();
            return response()->json([
                'message' => 'success',
                'introduction' => $introduction,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}


