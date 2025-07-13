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
        // die("aa");
        try{
            $data = $request->validated();
            dd(Auth::guard('customer')->user());
            $data['cust_id'] = Auth::guard('customer')->user()->cust_id;
            $address = $this->addressCustomerService->createAddressCustomer($data);
            return response()->json([
                'message' => 'success',
                'address' => $address,
            ], 200);
        }catch(\Exception $e){
            return response()->json([ 'messgae'=> $e->getMessage()]);
        }
    }
}
