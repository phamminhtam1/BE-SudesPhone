<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
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
}
