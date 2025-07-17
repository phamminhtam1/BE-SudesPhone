<?php

namespace App\Services;

use App\Models\Cart;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use PHPOpenSourceSaver\JWTAuth\Claims\Custom;

class CartService
{
    public function creatCart($cust_id){
        try{
            DB::beginTransaction();
            $cart = new Cart();
            $cart['cust_id'] = $cust_id;
            $cart->save();
            DB::commit();
            return $cart;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
