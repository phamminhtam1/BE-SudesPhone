<?php
namespace App\Http\Controllers;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    public function addNewUser(CreateUserRequest $request){
        try{
            $data = $request->validated();
            $user = $this->userService->addNewUser($data);
            return response()->json([
                'message' => 'sucsses',
                'user' => $user
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message'=> $e->getMessage()
            ], 500);
        }
    }

    public function getAllUser(Request $request){
        try{
            $users = $this->userService->getAllUser($request->query());
            return response()->json(['message'=>'success','users'=>$users], 200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage() ],500);
        }
    }

    public function getUser(User $user){
        try{
            $user = $this -> userService->getUser($user);
            return response()->json(['user'=>$user], 200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage() ],500);
        }
    }

    public function editUser(UpdateUserRequest $request ,User $user){
        try{
            $data = $request->validated();
            $user = $this ->userService ->updateUser($user, $data);
            return response()->json(['message'=>'success' ,'user'=>$user], 200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage() ],500);
        }
    }

    public function deleteUser(Request $request ,User $user){
        try{
            $user = $this -> userService ->deleteUser($user);
            return response()->json(['message'=> 'success' ,'user'=>$user], 200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage() ],500);
        }
    }

    public function updateUserStatus(Request $request, User $user){
        $validate = Validator::make($request->all(), [
            'status'=> 'required|integer|in:0,1',
        ]);

        if($validate->fails()){
            return response()->json($validate->errors(),422);
        }

        try{
            $user = $this->userService->updateUserStatus($user, $request->status);
            if ($user instanceof \Illuminate\Http\JsonResponse) {
                return $user;
            }
            return response()->json([['message'=> 'success','user'=> $user]], 200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage() ],500);
        }
    }
}
