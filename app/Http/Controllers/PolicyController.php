<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\PolicyService;

class PolicyController extends Controller
{
    protected $policyService;

    public function __construct(PolicyService $policyService)
    {
        $this->policyService = $policyService;
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if(!$admin){
            return response()->json(['message' => 'Không xác thực'], 401);
        }
        $validate = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 403);
        }

        try {
            $data = $validate->validated();
            $policy = $this->policyService->updatePolicy($data);
            return response()->json([
                'message' => 'success',
                'policy' => $policy,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function get()
    {
        try {
            $policy = $this->policyService->getPolicy();
            return response()->json([
                'message' => 'success',
                'policy' => $policy,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}


