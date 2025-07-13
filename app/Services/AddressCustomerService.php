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
            $address->line1 = $data['line1'];
            $address->line2 = $data['line2'];
            $address->city = $data['city'];
            $address->region = $data['region'];
            $address->country = $data['country'];
            $address->is_default = $data['is_default'];
            $address->save();
            DB::commit();
            return $address;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

}
