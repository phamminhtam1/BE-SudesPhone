<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'role_id',
        'name',
        'email',
        'phone',
        'hire_date',
        'salary',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(){
        return $this -> belongsTo(Role::class, 'role_id', 'id');
    }

    public function branch(){
        return $this -> belongsTo(Branch::class,'branch_id', 'branch_id');
    }

    public function createdReceipts(){
        return $this -> hasMany(StockReceipt::class,'user_id', 'id');
    }

    public function approvedReceipts(){
        return $this -> hasMany(StockReceipt::class,'user_id', 'id');
    }

    public function blogPosts(){
        return $this -> hasMany(BlogPost::class, 'author_emp_id', 'id');
    }
}
