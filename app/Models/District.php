<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $primaryKey = 'district_id';

    public $incrementing = false;

    protected $fillable = [
        'district_id',
        'name',
        'province_id'
    ];

    public function province(){
        return $this->belongsTo(Province::class, 'province_id','province_id');
    }

    public function wards(){
        return $this->hasMany(Ward::class,'district_id','district_id');
    }

}
