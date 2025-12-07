<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\AuthService;
use Carbon\CarbonInterface;
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
                    'expires_at' => optional($result['access_token_expires_at'])->toISOString(),
                ],
            ], 200)->withCookie(
                $this->makeRefreshCookie($result['refresh_token'], $result['refresh_token_expires_at'])
            );
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

    public function refreshToken(Request $request)
    {
        try {
            $refreshToken = $request->cookie($this->refreshCookieName());
            $result = $this->authService->refreshAccessToken($refreshToken);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'user' => $result['user'],
                    'access_token' => $result['access_token'],
                    'token_type' => $result['token_type'],
                    'expires_at' => optional($result['access_token_expires_at'])->toISOString(),
                ],
            ])->withCookie(
                $this->makeRefreshCookie($result['refresh_token'], $result['refresh_token_expires_at'])
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to refresh token', 500, $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $refreshToken = $request->cookie($this->refreshCookieName());
            $this->authService->logout($request->user(), $refreshToken);

            return ApiResponse::success(null, 'Logged out successfully')
                ->withCookie($this->forgetRefreshCookie());
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

    protected function makeRefreshCookie(string $token, CarbonInterface $expiresAt)
    {
        $config = $this->refreshCookieConfig();
        $lifetimeMinutes = max(1, (int) ceil(now()->diffInSeconds($expiresAt) / 60));

        return cookie(
            $config['name'],
            $token,
            $lifetimeMinutes,
            $config['path'],
            $config['domain'],
            $config['secure'],
            true,
            false,
            $config['same_site']
        );
    }

    protected function forgetRefreshCookie()
    {
        $config = $this->refreshCookieConfig();

        return cookie(
            $config['name'],
            null,
            -60,
            $config['path'],
            $config['domain'],
            $config['secure'],
            true,
            false,
            $config['same_site']
        );
    }

    protected function refreshCookieName(): string
    {
        return $this->refreshCookieConfig()['name'];
    }

    protected function refreshCookieConfig(): array
    {
        $config = config('auth.refresh_tokens') ?? [];

        return [
            'name' => $config['cookie_name'] ?? 'iot_core_refresh_token',
            'path' => $config['path'] ?? '/',
            'domain' => $config['domain'] ?? config('session.domain'),
            'secure' => $config['secure'] ?? false,
            'same_site' => $config['same_site'] ?? 'lax',
        ];
    }
}
