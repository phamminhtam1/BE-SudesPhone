<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\RoleService;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService){
        $this->roleService = $roleService;
    }

    public function addNewRole(Request $request){
        $validate = Validator::make($request->all(), [
            'name' =>'required|string',
            'description' =>'required|string'
        ]);

        if($validate->fails()){
            return response()->json($validate->errors(),403);
        }
        try{
            $data = $validate->validated();
            $role = $this->roleService->createRole($data);
            return response()->json([
                'message'=>'sucsses',
                'role'=> $role
            ], 200);
        }
        catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()],500);
        }

    }

    public function getAllRole(Request $request){
        try{
            $filters = $request->only('keyword');
            $roles = $this->roleService->getAllRoles($filters);
            return response()->json(['message'=>'success', 'roles'=>$roles],200);
        }
        catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }

    public function getRole(Role $role){
        try{
            $role = $this->roleService->getRole($role);
            return response()->json(['role'=> $role],200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }

    public function editRole(Request $request, Role $role){
        $validate = Validator::make($request->all(),[
            'name'=> 'required|string',
            'description' => 'required|string'
        ]);
        if($validate->fails()){
            return response()->json(['message'=> $validate->errors()],500);
        }
        try{
            $data = $validate->validated();
            $role = $this->roleService->updateRole($role, $data);
            return response()->json(['message'=> 'success','role'=>$role],200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }

    public function deleteRole(Role $role){
        try{
            $role = $this->roleService->deleteRole($role);
            return response()->json(['message'=> 'success','role'=>$role],200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }


    public function updateRoleStatus(Request $request, Role $role){
        $validate = Validator::make($request->all(), [
            'status' => 'required|integer|in:0,1'
        ]);
        if($validate->fails()){
            return response()->json($validate->errors(),403);
        }
        try{
            $role = $this->roleService->updateRoleStatus($role, $request->status);
            return response()->json(['message'=> 'success','role'=> $role],200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }
}
