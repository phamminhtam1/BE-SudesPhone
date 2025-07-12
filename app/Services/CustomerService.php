<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use PHPOpenSourceSaver\JWTAuth\Claims\Custom;

class CustomerService
{
    public function createCustomer(array $data){
        try{
            DB::beginTransaction();
            $customer = new Customer();
            $customer->first_name = $data['first_name'];
            $customer->last_name = $data['last_name'];
            $customer->email = $data['email'];
            $customer->phone = $data['phone'];
            $customer->password_hash = Hash::make($data['password']);
            $customer->save();
            DB::commit();
            return $customer;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function login(string $email, string $password){
        $customer = Customer::where('email', $email)->first();
        if(!$customer){
            return [
                'message' => 'Email ko đúng'
            ];
        }
        if(!Hash::check($password, $customer->password_hash)){
            return [
                'message'=> 'Mật khẩu không đúng!'
            ];
        }
        $token = $customer->createToken('customer-token')->plainTextToken;
        return [
            'token'=> $token,
            'customer' => $customer
        ];
    }

    public function getCustomers(){
        return Customer::get();
    }
}
