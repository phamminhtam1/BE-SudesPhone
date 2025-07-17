<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Customer\CreateAddressCustomerRequest;
use App\Http\Requests\Customer\EditAddressCustomerRequest;

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

    public function getAddressCustomer(Address $address){
        try{
            $cust_id = Auth::user()->cust_id;
            $address = $this->addressCustomerService->getAddressCustomer($address, $cust_id);
            return response()->json(['address' => $address]);
        }catch(\Exception $e){
            return response()->json([ 'message'=> $e->getMessage()]);
        }
    }

    public function editAddressCustomer(EditAddressCustomerRequest $request, Address $address){
        try{
            $cust_id = Auth::user()->cust_id;
            $data = $request->validated();
            $address = $this->addressCustomerService->updateAddressCustomer($address,$data, $cust_id);
            return response()->json([
                'message'=>'success',
                'address'=>$address
            ]);
        }catch(\Exception $e){
            return response()->json([ 'message'=> $e->getMessage()]);
        }
    }

    public function deleteAddressCustomer(Address $address){
        $cust_id = Auth::user()->cust_id;
        try{
            $this->addressCustomerService->deleteAddressCustomer($address, $cust_id);
            return response()->json(['message' => 'Delete success'], 200);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
