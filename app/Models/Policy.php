<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    protected $table = 'policies';
    protected $fillable = ['content'];
    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;
}
