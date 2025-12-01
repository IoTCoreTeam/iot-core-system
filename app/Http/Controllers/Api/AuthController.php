<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterUserRequest $request)
    {
        try {
            $this->authService->register($request->validated());

            return ApiResponse::success(null, ['message' => 'Registered successfully'], 201);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to register user', 500, $e->getMessage());
        }
    }

    public function login(LoginUserRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $result['user'],
                    'access_token' => $result['access_token'],
                    'token_type' => $result['token_type'],
                ],
            ], 200);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to login user', 500, $e->getMessage());
        }
    }

    public function user()
    {
        try {
            $user = $this->authService->getAuthenticatedUser();

            return ApiResponse::success($user);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to fetch user data', 500, $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());

            return ApiResponse::success(null, 'Logged out successfully');
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to logout', 500, $e->getMessage());
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $this->authService->changePassword($request->user(), $request->validated());

            return ApiResponse::success(null, 'Password changed successfully');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to change password', 500, $e->getMessage());
        }
    }
}
