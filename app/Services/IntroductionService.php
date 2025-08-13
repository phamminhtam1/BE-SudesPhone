<?php

namespace App\Services;

use App\Models\Introduction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IntroductionService
{
    public function getIntroduction(){
        return Introduction::first();
    }

    public function updateIntroduction(array $data){
        try{
            DB::beginTransaction();

            $affected = Introduction::query()->update([
                'title' => $data['title'] ?? null,
                'content' => $data['content'] ?? null,
            ]);

            if($affected === 0){
                Introduction::create([
                    'title' => $data['title'] ?? '',
                    'content' => $data['content'] ?? '',
                ]);
            }

            DB::commit();
            return Introduction::first();
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
