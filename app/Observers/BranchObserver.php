<?php

namespace App\Observers;

use App\Models\Branch;
use App\Models\User;

class BranchObserver
{
    /**
     * Handle the Branch "created" event.
     */
    public function created(Branch $branch): void
    {
        //
    }

    /**
     * Handle the Branch "updated" event.
     */
    public function updated(Branch $branch): void
    {
        $oldStatus = $branch->getOriginal('status');
        $newStatus = $branch->status;

        if($oldStatus === $newStatus){
            return;
        }

        if($newStatus  == 0){
            User::where('branch_id', $branch->branch_id)
                ->where('status',1)
                ->where('manually_disabled', false)
                ->update(['status'=> 0]);
        }

        if($newStatus == 1){
            User::where('branch_id', $branch->branch_id)
                ->where('status',0)
                ->where('manually_disabled', false)
                ->whereHas('role', function ($query) {
                    $query->where('status', 1);
                })
                ->update(['status'=> 1]);
            }
    }

    /**
     * Handle the Branch "deleted" event.
     */
    public function deleted(Branch $branch): void
    {
        //
    }

    /**
     * Handle the Branch "restored" event.
     */
    public function restored(Branch $branch): void
    {
        //
    }

    /**
     * Handle the Branch "force deleted" event.
     */
    public function forceDeleted(Branch $branch): void
    {
        //
    }
}
