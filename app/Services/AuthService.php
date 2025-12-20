<?php

namespace App\Services;

use App\Helpers\SystemLogHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Token as PassportToken;

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

            $tokens = $this->issueTokensForUser($user, true);

            SystemLogHelper::log('auth.login.success', 'User logged in successfully', [
                'user_id' => $user->id,
            ]);

            return $tokens;
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

    public function refreshAccessToken(?string $refreshToken): array
    {
        try {
            $plainToken = trim((string) $refreshToken);

            if ($plainToken === '') {
                throw ValidationException::withMessages([
                    'refresh_token' => ['Refresh token is missing or invalid.'],
                ]);
            }

            $hashedId = $this->hashRefreshToken($plainToken);
            $record = DB::table('oauth_refresh_tokens')->where('id', $hashedId)->first();

            if (! $record || $record->revoked) {
                SystemLogHelper::log('auth.refresh.failed', 'Refresh token is invalid or revoked', [
                    'refresh_token_id' => $hashedId,
                ], ['level' => 'warning']);

                throw ValidationException::withMessages([
                    'refresh_token' => ['The provided refresh token is invalid.'],
                ]);
            }

            $expiresAt = $record->expires_at ? Carbon::parse($record->expires_at) : null;
            if ($expiresAt && $expiresAt->isPast()) {
                $this->markRefreshTokenAsRevoked($hashedId);
                SystemLogHelper::log('auth.refresh.expired', 'Refresh token expired', [
                    'refresh_token_id' => $hashedId,
                ], ['level' => 'notice']);

                throw ValidationException::withMessages([
                    'refresh_token' => ['Refresh token has expired. Please login again.'],
                ]);
            }

            /** @var PassportToken|null $oldAccessToken */
            $oldAccessToken = PassportToken::query()->where('id', $record->access_token_id)->first();

            if (! $oldAccessToken || $oldAccessToken->revoked) {
                $this->markRefreshTokenAsRevoked($hashedId);

                throw ValidationException::withMessages([
                    'refresh_token' => ['Associated access token is no longer valid.'],
                ]);
            }

            $user = User::find($oldAccessToken->user_id);

            if (! $user) {
                $this->markRefreshTokenAsRevoked($hashedId);

                throw ValidationException::withMessages([
                    'refresh_token' => ['Unable to locate the user for this token.'],
                ]);
            }

            DB::transaction(function () use ($oldAccessToken, $hashedId) {
                $oldAccessToken->revoke();
                $this->markRefreshTokenAsRevoked($hashedId);
            });

            $tokens = $this->issueTokensForUser($user);

            SystemLogHelper::log('auth.refresh.success', 'Issued new access token via refresh token', [
                'user_id' => $user->id,
            ]);

            return $tokens;
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            SystemLogHelper::log('auth.refresh.error', 'Failed to refresh access token', [
                'error' => $e->getMessage(),
            ], ['level' => 'error']);

            throw $e;
        }
    }

    public function getAuthenticatedUser(): User
    {
        try {
            $user = User::where('id', Auth::id())->firstOrFail();

            return $user;
        } catch (\Throwable $e) {
            SystemLogHelper::log('auth.user.fetch_failed', 'Failed to fetch user data', [
                'auth_id' => Auth::id(),
                'error' => $e->getMessage(),
            ], ['level' => 'error']);

            throw $e;
        }
    }

    public function logout(?User $authUser, ?string $refreshToken = null): void
    {
        try {
            DB::transaction(function () use ($authUser, $refreshToken) {
                if ($authUser && $authUser->token()) {
                    $authUser->token()->revoke();

                    SystemLogHelper::log('auth.logout.success', 'User logged out successfully', [
                        'user_id' => $authUser->id,
                    ]);
                } elseif (! $authUser) {
                    SystemLogHelper::log('auth.logout.missing_user', 'Logout called without an authenticated user', [], [
                        'level' => 'notice',
                    ]);
                }

                if ($refreshToken) {
                    $this->revokeRefreshToken($refreshToken);
                }
            });
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

    protected function issueTokensForUser(User $user, bool $trackLogin = false): array
    {
        if ($trackLogin) {
            Auth::login($user);
        }

        $tokenResult = $user->createToken('frontend-token', $this->scopesForUser($user));
        $accessTokenModel = $tokenResult->token;
        $refreshData = $this->createRefreshToken($accessTokenModel);

        return [
            'user' => $user,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'access_token_expires_at' => $accessTokenModel->expires_at ? Carbon::parse($accessTokenModel->expires_at) : null,
            'refresh_token' => $refreshData['token'],
            'refresh_token_expires_at' => $refreshData['expires_at'],
        ];
    }

    protected function createRefreshToken(PassportToken $accessToken): array
    {
        $plainToken = Str::random(64);
        $hashedToken = $this->hashRefreshToken($plainToken);
        $expiresAt = Carbon::now()->addDays($this->refreshTokenTtlDays());

        DB::table('oauth_refresh_tokens')->insert([
            'id' => $hashedToken,
            'access_token_id' => $accessToken->id,
            'revoked' => false,
            'expires_at' => $expiresAt,
        ]);

        return [
            'token' => $plainToken,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Build the default scopes that should be baked into the issued token.
     *
     * Embedding the role as a scope lets downstream services decode the JWT and
     * determine the role without additional database calls.
     *
     * @return list<string>
     */
    protected function scopesForUser(User $user): array
    {
        $role = trim((string) ($user->role ?? ''));

        if ($role === '') {
            return [];
        }

        $normalizedRole = (string) str($role)->lower()->snake();

        return ["role:{$normalizedRole}"];
    }

    protected function refreshTokenTtlDays(): int
    {
        $config = config('auth.refresh_tokens');

        return (int) ($config['ttl_days'] ?? 30);
    }

    protected function hashRefreshToken(string $token): string
    {
        return hash('sha256', $token);
    }

    protected function revokeRefreshToken(string $refreshToken): void
    {
        $hashedId = $this->hashRefreshToken($refreshToken);
        $this->markRefreshTokenAsRevoked($hashedId);
    }

    protected function markRefreshTokenAsRevoked(string $hashedId): void
    {
        DB::table('oauth_refresh_tokens')
            ->where('id', $hashedId)
            ->update([
                'revoked' => true,
                'expires_at' => Carbon::now(),
            ]);
    }
}
