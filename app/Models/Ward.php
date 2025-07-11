<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $primaryKey = 'wards_id';

    public $incrementing = false;

    protected $fillable = [
        'wards_id',
        'district_id',
        'name',
    ];

    public function district(){
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }
}
