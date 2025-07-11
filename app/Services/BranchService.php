<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BranchService
{
    public function createBranch(array $data, array $files = [])
    {
        try {
            $branch = new Branch();
            $branch->name = $data['name'];
            $branch->phone = $data['phone'];
            $branch->email = $data['email'];
            $branch->address_line1 = $data['address_line1'];
            $branch->city = $data['city'];
            $branch->save();
            foreach ($files as $file) {
                $path = $file->store("branches/{$branch->branch_id}", 'public');
                $branch->images()->create([
                    'path' => $path,
                    'type' => 'original',
                ]);
            }
            return $branch->load('images');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAllBranches($filters = [])
    {
        $query = Branch::query()
            ->with('images');
        if (!empty($filters['keyword'])) {
            $query->where('name','like','%'. $filters['keyword'] .'%');
        }
        return $query->get();
    }

    public function getBranch(Branch $branch)
    {
        return $branch->load('images');
    }

    public function updateBranch(Branch $branch, array $data, array $files = [])
    {
        try {
            DB::beginTransaction();
            $branch->name = $data['name'];
            $branch->phone = $data['phone'];
            $branch->email = $data['email'];
            $branch->address_line1 = $data['address_line1'];
            $branch->city = $data['city'];
            $branch->save();
            if (!empty($files)) {
                foreach ($branch->images as $image) {
                    try {
                        if (Storage::disk('public')->exists($image->path)) {
                            Storage::disk('public')->delete($image->path);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to delete image: ' . $e->getMessage());
                    }
                    $image->delete();
                }
                foreach ($files as $file) {
                    $path = $file->store("branches/{$branch->branch_id}", 'public');
                    $branch->images()->create([
                        'path' => $path,
                        'type' => 'original',
                    ]);
                }
            }
            DB::commit();
            return $branch->load('images');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteBranch(Branch $branch)
    {
        try {
            DB::beginTransaction();
            foreach ($branch->images as $image) {
                try {
                    if (Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to delete image: ' . $e->getMessage());
                }
            }
            $branch->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateBranchStatus(Branch $branch, int $status)
    {
        try {
            $branch->status = $status;
            $branch->save();
            return $branch;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
