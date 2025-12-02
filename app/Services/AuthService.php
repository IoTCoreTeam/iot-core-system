<?php

namespace App\Services;

use App\Helpers\SystemLogHelper;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $userData): User
    {
        try {
            $user = User::create($userData);

            SystemLogHelper::log('auth.register.success', 'User registered successfully', [
                'registered_user_id' => $user->id,
                'email' => $user->email,
            ]);

            return $user;
        } catch (\Throwable $e) {
            SystemLogHelper::log('auth.register.failed', 'Failed to register user', [
                'email' => $userData['email'] ?? null,
                'error' => $e->getMessage(),
            ], ['level' => 'error']);

            throw $e;
        }
    }

    public function login(array $credentials): array
    {
        try {
            $user = User::where('email', $credentials['email'])->first();

            SystemLogHelper::log('auth.login.attempt', 'Login attempt received', [
                'email' => $credentials['email'],
                'user_exists' => (bool) $user,
            ]);

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                SystemLogHelper::log('auth.login.failed', 'Login failed due to invalid credentials', [
                    'email' => $credentials['email'],
                ], ['level' => 'warning']);

                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            Auth::login($user);

            $token = $user->createToken('frontend-token')->accessToken;

            SystemLogHelper::log('auth.login.success', 'User logged in successfully', [
                'user_id' => $user->id,
            ]);

            return [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            SystemLogHelper::log('auth.login.error', 'Failed to login user', [
                'email' => $credentials['email'] ?? null,
                'error' => $e->getMessage(),
            ], ['level' => 'error']);

            throw $e;
        }
    }

    public function getAuthenticatedUser(): User
    {
        try {
            $user = User::where('id', Auth::id())->firstOrFail();

            SystemLogHelper::log('auth.user.fetched', 'Fetched authenticated user data', [
                'user_id' => $user->id,
            ]);

            return $user;
        } catch (\Throwable $e) {
            SystemLogHelper::log('auth.user.fetch_failed', 'Failed to fetch user data', [
                'auth_id' => Auth::id(),
                'error' => $e->getMessage(),
            ], ['level' => 'error']);

            throw $e;
        }
    }

    public function logout(?User $authUser): void
    {
        try {
            if ($authUser) {
                $authUser->token()->revoke();

                SystemLogHelper::log('auth.logout.success', 'User logged out successfully', [
                    'user_id' => $authUser->id,
                ]);
            } else {
                SystemLogHelper::log('auth.logout.missing_user', 'Logout called without an authenticated user', [], [
                    'level' => 'notice',
                ]);
            }
        } catch (\Throwable $e) {
            SystemLogHelper::log('auth.logout.failed', 'Failed to logout', [
                'user_id' => $authUser?->id,
                'error' => $e->getMessage(),
            ], ['level' => 'error']);

            throw $e;
        }
    }

    public function changePassword(User $user, array $payload): void
    {
        try {
            DB::transaction(function () use ($user, $payload) {
                if (! Hash::check($payload['current_password'], $user->password)) {
                    SystemLogHelper::log('auth.password.invalid_current', 'Password change rejected due to invalid current password', [
                        'user_id' => $user->id,
                    ], ['level' => 'warning']);

                    throw ValidationException::withMessages([
                        'current_password' => ['The current password is incorrect.'],
                    ]);
                }

                $user->forceFill([
                    'password' => $payload['new_password'],
                ])->save();

                SystemLogHelper::log('auth.password.changed', 'User changed password successfully', [
                    'user_id' => $user->id,
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            SystemLogHelper::log('auth.password.failed', 'Failed to change password', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ], ['level' => 'error']);

            throw $e;
        }
    }
}
