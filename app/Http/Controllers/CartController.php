<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Cart\CreateCartRequest;
use App\Models\CartItem;
use PDO;

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

    public function updateQuantity(Request $request, CartItem $cartItem){
        $cust_id = Auth::user()->cust_id;
        if($cartItem->cart->cust_id !== $cust_id){
            return response()->json(['message' => 'Bạn không có quyền !'],403);
        }
        $qty = $request->input('qty');
        try{
            $cartItem = $this->cartService->updateQuantity($cartItem, $qty);
            return response()->json([
                'message' => 'success',
                'cartitem' => $cartItem
            ], 200);
        }catch(\Exception $e){
            return response()->json([ 'message'=> $e->getMessage()]);
        }

    }

    public function deleteItem(CartItem $cartItem){
        $cust_id = Auth::user()->cust_id;
        if($cartItem->cart->cust_id !== $cust_id){
            return response()->json(['message' => 'Bạn không có quyền !'],403);
        }
        try{
            $this->cartService->deleteCartItem($cartItem);
            return response()->json(['message' => 'Delete success'], 200);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
