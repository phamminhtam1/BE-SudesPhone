<?php

namespace App\Services;

use App\Models\Policy;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PolicyService
{
    public function getPolicy(){
        return Policy::first();
    }

    public function updatePolicy(array $data){
        try{
            DB::beginTransaction();

            $affected = Policy::query()->update([
                'content' => $data['content'] ?? null,
            ]);

            if($affected === 0){
                Policy::create([
                    'content' => $data['content'] ?? '',
                ]);
            }

            DB::commit();
            return Policy::first();
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
