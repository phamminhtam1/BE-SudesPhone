<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierService
{
    public function createSupplier(array $data){
        try{
            DB::beginTransaction();
            $supplier = new Supplier();
            $supplier->name = $data['name'];
            $supplier->email = $data['email'];
            $supplier->phone = $data['phone'];
            $supplier->address = $data['address'];
            $supplier->save();

            DB::commit();
            return $supplier;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
    public function getAllSupplier($filters = []){
        $query = Supplier::query();
        if(!empty($filters['keyword'])){
            $query->where('name','like','%'.$filters['keyword'].'%');
        }
        return $query->get();
    }

    public function getSupplier(Supplier $supplier){
        return $supplier;
    }

    public function updateSupplier(Supplier $supplier, array $data){
        try{
            DB::beginTransaction();
            $supplier->name = $data['name'];
            $supplier->email = $data['email'];
            $supplier->phone = $data['phone'];
            $supplier->address = $data['address'];
            $supplier->save();
            DB::commit();
            return $supplier;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStatusSupplier(Supplier $supplier, int $status){
        try{
            $supplier->status = $status;
            $supplier->save();
            return $supplier;
        }catch(\Exception $e){
            throw $e;
        }
    }

    public function deleteSupplier(Supplier $supplier){
        try{
            DB::beginTransaction();
            $supplier->delete();
            DB::commit();
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
