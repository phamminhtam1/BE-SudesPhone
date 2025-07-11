<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleService
{
    public function createRole(array $data){
       try{
        $role = new Role();
        $role->name = $data['name'];
        $role->description = $data['description'];
        $role->save();
        return $role;
       }
       catch(\Exception $e){
        throw $e;
       }
    }

    public function getAllRoles($filters = []){
        $query = Role::query();
        if(!empty($filters['keyword'])){
            $query->where('name','like','%'. $filters['keyword'] .'%');
        }
        return $query->get();
    }

    public function getRole( Role $role ){
        return $role;
    }

    public function updateRole(Role $role, array $data){
        try{
            DB::beginTransaction();
            $role->name = $data['name'];
            $role->description = $data['description'];
            $role->save();

            DB::commit();
            return $role;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteRole(Role $role){
        try{
            DB::beginTransaction();
            $role->delete();
            DB::commit();
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRoleStatus(Role $role, int $status){
        try {
            $role->status = $status;
            $role->save();
            return $role;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
