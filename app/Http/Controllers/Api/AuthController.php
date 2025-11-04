<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\RegisterUserRequest;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        // chuẩn code
        // validate ở request
        // lệnh create chuẩn:
        $user = User::create($request->validated());
        // return chuẩn (hàm error và success đã được định nghĩa trong base controller (controller.php))
        if(! $user) {
            return $this->error('Registration failed', 500);
        }
        return $this->success(['message' => 'Registered successfully'], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('frontend-token')->accessToken;

        return $this->success(['access_token' => $token, 'token_type' => 'Bearer', 'user' => $user]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Logged out']);
    }
}
