<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Customer\CreateAddressCustomerRequest;
use App\Models\Address;
use App\Services\AddressCustomerService;
use Illuminate\Support\Facades\Auth;

class AddressCustomerController extends Controller
{
    protected $addressCustomerService;
    public function __construct(AddressCustomerService $addressCustomerService){
        $this->addressCustomerService = $addressCustomerService;
    }

    public function addNewAddressCustomer(CreateAddressCustomerRequest $request){
        try{
            $data = $request->validated();
            $data['cust_id'] = Auth::user()->cust_id;
            // dd($data['cust_id']);
            $address = $this->addressCustomerService->createAddressCustomer($data);
            return response()->json([
                'message' => 'success',
                'address' => $address,
            ], 200);
        }catch(\Exception $e){
            return response()->json([ 'message'=> $e->getMessage()]);
        }
    }

    public function getAllAddressCustomer(){
        try{
            $address = $this->addressCustomerService->getAllAddressCustomer();
            return response()->json([
                'message' => 'success',
                'address' => $address
            ], 200);
        }catch(\Exception $e){
            return response()->json([ 'message'=> $e->getMessage()]);
        }
    }
}
