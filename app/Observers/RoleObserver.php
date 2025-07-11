<?php

namespace App\Observers;

use App\Models\Role;
use App\Models\User;

class RoleObserver
{
    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        $oldStatus = $role->getOriginal('status');
        $newStatus = $role->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        if ($newStatus == 0){
            User::where('role_id', $role->id)
                ->where('status', 1)
                ->where('manually_disabled', false)
                ->update(['status'=> 0]);
        }

        if($newStatus == 1){
            User::where('role_id', $role->id)
                ->where('status', 0)
                ->where('manually_disabled', false)
                ->whereHas('branch', function ($query) {
                    $query->where('status', 1);
                })
                ->update(['status'=> 1]);
        }
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "force deleted" event.
     */
    public function forceDeleted(Role $role): void
    {
        //
    }
}
