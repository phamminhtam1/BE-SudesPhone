<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchImage extends Model
{
    /**
     * The table associated with the model.
     * (Optional if follows naming convention: branch_images)
     *
     * @var string
     */
    protected $table = 'branch_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_id',
        'path',
        'type',
    ];

    /**
     * Relationship: an image belongs to a branch.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }
}
