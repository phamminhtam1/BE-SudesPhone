<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Province;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function provinces(){
        return Province::select('province_id as id', 'name') ->orderBy('name') ->get();
    }

    public function districts(Province $province){
        return $province ->districts() ->select('district_id as id', 'name') ->orderBy('name') ->get();
    }

    public function wards(District $district){
        return $district ->wards() ->select('wards_id as id', 'name')->orderBy('name') ->get();
    }
}
