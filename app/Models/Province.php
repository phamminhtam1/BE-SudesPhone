<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $primaryKey = 'province_id';
    public $incrementing = false;

    protected $fillable = [
        'province_id',
        'name',
    ];

    public function districts(){
        return $this->hasMany(District::class, 'province_id', 'province_id');
    }
}
