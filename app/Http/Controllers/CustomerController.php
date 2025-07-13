<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Customer\CreateCustomerRequest;
use App\Http\Requests\Customer\LoginCustomerRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use App\Services\CustomerService;

class CustomerController extends Controller
{
    protected $customerService;
    public function __construct(CustomerService $customerService){
        $this->customerService = $customerService;
    }

    public function addNewCustomer(CreateCustomerRequest $request){
        try{
            $data = $request->validated();
            $customer = $this->customerService->createCustomer($data);
            return response()->json([
                "success"=> 'success',
                "customer"=> $customer
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }
    public function login(LoginCustomerRequest $request){
        try{
            $data = $request->validated();
            $customer = $this->customerService->login($data['email'], $data['password']);
            if(!$customer){
                return response()->json(['message'=> 'Email hoặc mật khẩu không đúng'], 402);
            }
            return response()->json([
                'message'=> 'success',
                'customer' =>$customer
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }
    public function getAllCustomer(){
        try{
            $customer = $this->customerService->getCustomers();
            return response()->json([
                'message' => 'success',
                'customers' => $customer
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

    public function getCustomer(Customer $customer){
        try{
            $customer = $this->customerService->getCustomer($customer);
            return response()->json([
                'message'=> 'success',
                'customer' => $customer
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

}
