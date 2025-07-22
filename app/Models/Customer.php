<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $primaryKey = 'cust_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password_hash',
    ];

    public function address(){
        return $this->hasMany(Address::class, 'cust_id', 'cust_id');
    }
    public function order(){
        return $this->hasMany(Order::class, 'cust_id', 'cust_id');
    }
}
