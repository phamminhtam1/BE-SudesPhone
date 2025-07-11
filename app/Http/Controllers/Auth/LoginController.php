<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        if ($validate->fails()){
            return response()->json($validate->errors(), 403);
        }
        $credentials = ['email'=>$request->email, 'password'=>$request->password];

        try {
            if (!Auth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 403);
            }
            $user = User::where('email',operator: $request->email)->firstOrFail();
            $token = $user->createToken('auth_token', ['*'], now()->addMinutes(30))->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'user' => $user,
                'expires_in' => 30 * 60, // 30 minutes in seconds
                'expires_at' => now()->addMinutes(30)->toISOString()
            ], 200);
        }catch (\Exception $e){
            return response()->json(['error'=> $e->getMessage()], 403);
        }
    }

    /**
     * Logout the authenticated user by deleting their current access token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request){
        /** @var PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        $token->delete();
        return response()->json(['message'=> 'user has been logged out'],200);
    }
}
