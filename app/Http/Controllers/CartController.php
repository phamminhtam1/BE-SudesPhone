<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Cart\CreateCartRequest;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService){
        $this->cartService = $cartService;
    }

    public function addNewCart(Request $request){
        $cust_id = Auth::user()->cust_id;
        try{
            $data = $request->all();
            $cart = $this->cartService->createCart($data, $cust_id);
            return response()->json([
                'message'=>'succes',
                'cart' => $cart
            ], 200);
        }catch(\Exception $e){
            return response()->json([ 'message'=> $e->getMessage()]);
        }
    }

    public function getMyCart(){
        $cust_id = Auth::user()->cust_id;
        try{
            $cart = $this->cartService->getMyCart($cust_id);
            return response()->json([
                'message' => 'success',
                'cart'=>$cart,
            ], 200);
        }catch(\Exception $e){
            return response()->json([ 'message'=> $e->getMessage()]);
        }
    }
}
