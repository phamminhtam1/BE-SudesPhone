<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Branch;
use App\Services\BranchService;

class BranchController extends Controller
{
    protected $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    public function addNewBranch(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'name' => 'required|string',
            'phone' => 'required|string',
            'email'=> 'required|email',
            'address_line1' => 'required|string',
            'city'=> 'required|string',
            'images'=> 'nullable|array',
            'images.*'=> 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        if($validate->fails()){
            return response()->json($validate->errors(), 403);
        }
        try {
            $data = $validate->validated();
            $files = $data['images'] ?? [];
            unset($data['images']);
            $branch = $this->branchService->createBranch($data, $files);
            return response()->json([
                'message' => 'success',
                'branch' => $branch,
            ], 201);
        } catch(\Exception $e) {
            return response()->json(['message'=> $e->getMessage()], 500);
        }
    }

    public function getAllBranch(Request $request)
    {
        try {
            $filters = $request->only('keyword');
            $branches = $this->branchService->getAllBranches($filters);
            return response()->json(['branch' => $branches], 200);
        } catch(\Exception $e) {
            return response()->json(['message'=> $e->getMessage()], 500);
        }
    }

    public function getBranch(Branch $branch)
    {
        try {
            $branch = $this->branchService->getBranch($branch);
            return response()->json([
                'branch' => $branch
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['message'=> $e->getMessage()], 500);
        }
    }

    public function editBranch(Request $request, Branch $branch)
    {
        $validate = Validator::make($request->all(),[
            'name' => 'required|string',
            'phone' => 'required|string',
            'email'=> 'required|email',
            'address_line1' => 'required|string',
            'city'=> 'required|string',
            'images'          => 'nullable|array',
            'images.*'        => 'image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:min_width=100,min_height=100',
        ]);
        if($validate->fails()){
            return response()->json($validate->errors(), 403);
        }
        try {
            $data = $validate->validated();
            $files = $data['images'] ?? [];
            unset($data['images']);
            $branch = $this->branchService->updateBranch($branch, $data, $files);
            return response()->json([
                'message' => 'Branch updated successfully',
                'branch' => $branch,
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteBranch(Branch $branch)
    {
        try {
            $this->branchService->deleteBranch($branch);
            return response()->json([
                'message' => 'Branch deleted successfully'
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateBranchStatus(Request $request, Branch $branch)
    {
        $validate = Validator::make($request->all(), [
            'status' => 'required|integer|in:0,1'
        ]);
        if($validate->fails()) {
            return response()->json($validate->errors(), 403);
        }
        try {
            $branch = $this->branchService->updateBranchStatus($branch, $request->status);
            return response()->json([
                'message' => 'Branch status updated successfully',
                'branch' => $branch
            ], 200);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
