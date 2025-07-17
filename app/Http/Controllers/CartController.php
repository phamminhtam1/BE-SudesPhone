<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService){
        $this->cartService = $cartService;
    }

    public function addNewCart(){
        $cust_id = Auth::user()->cust_id;
        try{
            $cart = $this->cartService->creatCart($cust_id);
            return response()->json([
                'message'=>'succes',
                'cart' => $cart
            ], 200);
        }catch(\Exception $e){
            return response()->json([ 'message'=> $e->getMessage()]);
        }
    }
}
