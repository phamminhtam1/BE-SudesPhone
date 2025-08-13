<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Introduction extends Model
{
    protected $table = 'introductions';
    protected $fillable = ['title', 'content'];
    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;
}
