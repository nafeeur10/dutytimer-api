<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Auth\Authenticatable;

class AuthController extends Controller
{

    /** @param User $user */
    public function __construct(
        private ?Authenticatable $user
    ) {
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password)
        ]);
        if($user) return response()->json([
            'data' => $user,
            'message' => 'User created successfully'
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator,
                'message' => 'Email or Password is not correct!'
            ], 422);
        }

        /** @var User|null $user */
        $user = User::firstWhere('email', $request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => $validator,
                'message' => 'Email or Password is not correct!'
            ], 422);
        }

        $token = $user->createToken(config('app.name'))->plainTextToken;
        return response()->json([
            'errors'  => null,
            'token'   => $token,
            'message' => 'Login Successful'
        ], 200);
    }

    public function logout(Request $request)
    {
        $this->user?->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout Successful'
        ], 200);
    }
}
