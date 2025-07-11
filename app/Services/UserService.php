<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function addNewUser(array $data){
        try{
            $user = new User();
            $user->branch_id = $data['branch_id'];
            $user->role_id = $data['role_id'];
            $user->name= $data['name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'];
            $user->hire_date = $data['hire_date'];
            $user->salary = $data['salary'];
            $user->password = Hash::make($data['password']);
            $user->save();
            return $user;
        }catch(\Exception $e){
            Log::error('error'. $e->getMessage());
        }
    }

    public function getAllUser($filters = []){
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 20;
        $query = User::query()
            ->with(['branch:branch_id,name', 'role:id,name']);
        if(!empty($filters['keyword'])){
            $query->where('name','like','%'.$filters['keyword'].'%');
        }
        if(!empty($filters['branch_id'])){
            $query->where('branch_id', $filters['branch_id']);
        }
        if(!empty($filters['role_id'])){
            $query->where('role_id', $filters['role_id']);
        }
        if(isset($filters['status'])){
            $query->where('status', $filters['status']);
        }
        if(!empty($filters['hire_date_from'])){
            $query->where('hire_date','>=', $filters['hire_date_from']);
        }
        return $query->orderByDesc('created_at')->paginate($perPage)->appends($filters);
    }

    public function getUser(User $user){
        return $user->load([
            'branch:branch_id,name',
            'role:id,name'
        ]);
    }

    public function updateUser(User $user, array $data){
        try{
            DB::beginTransaction();
            $user->branch_id = $data['branch_id'];
            $user->role_id = $data['role_id'];
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'];
            $user->hire_date = $data['hire_date'];
            $user->salary = $data['salary'];
            $user->save();
            DB::commit();
            return $user;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteUser(User $user){
        try{
            DB::beginTransaction();
            $user->delete();
            DB::commit();
            return $user;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function updateUserStatus(User $user, int $status){
        try{
            if ($status === 1) {
                if ($user->branch?->status !== 1 || $user->role?->status !== 1) {
                    // Nếu branch hoặc role đang bị tắt, thì không cho phép bật user
                    return response()->json([
                        'message' => 'Không thể bật user vì chi nhánh hoặc vai trò đang bị tắt.'
                    ], 422);
                }
            }

            $user->status = $status;
            $user->manually_disabled = $status === 0;
            $user->save();
            return $user;
        }catch(\Exception $e){
            Log::error('error'. $e->getMessage());
        }
    }
}
