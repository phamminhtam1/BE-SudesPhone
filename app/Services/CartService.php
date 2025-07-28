<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use PHPOpenSourceSaver\JWTAuth\Claims\Custom;

class CartService
{
    public function createCart(array $data, $cust_id){
        try{
            DB::beginTransaction();
            $cart = Cart::where('cust_id', $cust_id)->first();
            if(!$cart){
                $cart = new Cart();
                $cart['cust_id'] = $cust_id;
                $cart->save();
            }
            $cart_items = CartItem::where('cart_id', $cart->cart_id)
                                    ->where('prod_id', $data['prod_id'])
                                    ->first();
            if($cart_items){
                $cart_items->qty += $data['qty'];
                $cart_items->save();
            }
            else{
                $cart_items = new CartItem();
                $cart_items->cart_id = $cart->cart_id;
                $cart_items->prod_id = $data['prod_id'];
                $cart_items->qty = $data['qty'];
                $cart_items->save();
            }
            DB::commit();
            return $cart->load('items');
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function getMyCart($cust_id){
        $cart = Cart::with([
            'items.product' => function($query){
                $query->select('prod_id', 'name', 'discount_price');
            },
            'items.product.images' => function($query){
                $query->select('img_id', 'prod_id', 'img_url')->orderBy('img_id');
            },
            'items.product.specs' => function($query){
                $query->select('prod_id', 'spec_key', 'spec_value')
                    ->where('spec_key', 'Màu sắc');
            }
        ])->where('cust_id', $cust_id)->get();

        return $cart;
    }

    public function updateQuantity(CartItem $cartItem, $qty){
        try{
            DB::beginTransaction();
            $cartItem->qty = $qty;
            $cartItem->save();
            DB::commit();
            return $cartItem;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteCartItem(CartItem $cartItem){
        try{
            DB::beginTransaction();
            $cartItem->delete();
            DB::commit();
            return;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function clearCart($cust_id){
        try{
            DB::beginTransaction();
            $cart = Cart::where('cust_id', $cust_id)->first();
            if($cart){
                CartItem::where('cart_id', $cart->cart_id)->delete();
            }
            DB::commit();
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
