<?php

namespace App\Services;

use App\Models\Address;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use PHPOpenSourceSaver\JWTAuth\Claims\Custom;

class AddressCustomerService
{
    public function createAddressCustomer(array $data){
        try{
            DB::beginTransaction();
            $address = new Address();
            if ($data['is_default'] == '1') {
                Address::where('cust_id', $data['cust_id'])->update(['is_default' => false]);
            }
            $address->cust_id = $data['cust_id'];
            $address->label = $data['label'];
            $address->line = $data['line'];
            $address->city = $data['city'];
            $address->region = $data['region'];
            $address->ward = $data['ward'];
            $address->is_default = $data['is_default'];
            $address->save();
            DB::commit();
            return $address;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function getAllAddressCustomer(){
        $customer = auth('customer')->user();
        if (!$customer) {
            return collect(); // hoặc trả về []
        }
        return Address::where('cust_id', $customer->cust_id)->get();
    }

    public function getAddressCustomer(Address $address, $cust_id){
        if($address->cust_id == $cust_id){
            return $address;
        }
        throw new \Exception('Bạn không có quyền truy cập địa chỉ này.');
    }

    public function updateAddressCustomer(Address $address, array $data, $cust_id){
        if($address->cust_id == $cust_id){
            try{
                DB::beginTransaction();
                if ($data['is_default'] == '1') {
                    Address::where('cust_id', $cust_id)->update(['is_default' => false]);
                }
                $address->fill([
                    'cust_id' => $cust_id,
                    'label' => $data['label'],
                    'line' => $data['line'],
                    'city' => $data['city'],
                    'region' => $data['region'],
                    'ward' => $data['ward'],
                    'is_default' => $data['is_default'],
                ]);
                $address->save();
                DB::commit();
                return $address;
            }catch(\Exception $e){
                DB::rollBack();
                throw $e;
            }
        }
        throw new \Exception('Bạn không có quyền truy cập địa chỉ này.');
    }

}
