<?php

namespace App\Http\Controllers;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\SupplierService;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService){
        $this->supplierService = $supplierService;
    }

    public function addNewSupplier(Request $request){
        $validate = Validator::make($request->all(), [
            'name'=>'required|string',
            'phone' => ['required', 'regex:/^(0|\\+84)[0-9]{9}$/'],
            'email'=>'required|string',
            'address'=> 'required|string',
        ]);
        if($validate->fails()){
            return response()->json($validate->errors(),403);
        }
        try{
            $data = $validate->validated();
            $supplier = $this->supplierService->createSupplier($data);
            return response()->json([
                'message'=>'success',
                'supplier'=>$supplier
            ],200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()] ,500);
        }
    }

    public function getAllSuppliers(Request $request){
        $filter = $request->only('keyword');
        $supplier = $this->supplierService->getAllSupplier($filter);
        return response()->json(['message'=>'success', 'supplier'=>$supplier],200);
    }

    public function getSupplier(Supplier $supplier){
        try{
            $supplier = $this->supplierService->getSupplier($supplier);
            return response()->json([
                'message'=> 'success',
                'supplier'=>$supplier]
            ,200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }

    public function editSupplier(Request $request, Supplier $supplier){
        $validate = Validator::make($request->all(), [
            'name'=>'required|string',
            'phone' => ['required', 'regex:/^(0|\\+84)[0-9]{9}$/'],
            'email'=>'required|string',
            'address'=> 'required|string',
        ]);
        if($validate->fails()){
            return response()->json(['message'=> $validate->errors()],402);
        }
        try{
            $data = $validate->validated();
            $supplier = $this->supplierService->updateSupplier($supplier, $data);
            return response()->json(['message'=> 'success','supplier'=>$supplier],200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }

    public function updateStatusSupplier(Request $request, Supplier $supplier){
        $validate = Validator::make($request->all(), [
            'status' => 'required|integer|in:0,1'
        ]);
        if($validate->fails()){
            return response()->json(['message'=> $validate->errors()],402);
        }
        try{
            $supplier = $this->supplierService->updateStatusSupplier($supplier, $request->status);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }

    public function deleteSupplier(Supplier $supplier){
        try{
            $supplier = $this->supplierService->deleteSupplier($supplier);
            return response()->json([
                'message'=> 'success',
                'supplier'=>$supplier
                ],200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }

}
