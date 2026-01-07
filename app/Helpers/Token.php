<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Auth\User;
use Illuminate\Support\Str;
use App\Response\ResponseHelper;
use Firebase\JWT\ExpiredException;

class Token
{
    public static function createToken(User $user): string
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role->name,
            'iat' => now()->timestamp,
            'exp' => now()->addDays(7)->timestamp,
        ];

        return JWT::encode($payload, config('app.access_token_secret'), 'HS512');
    }

    public static function generateRefreshToken(User $user): array
    {
        $jti = (string) Str::uuid();
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role->name,
            'jti' => $jti,
            'iat' => now()->timestamp,
            'exp' => now()->addDays(7)->timestamp,
        ];

        $token = JWT::encode($payload, config('app.refresh_token_secret'), 'HS512');

        return [
            'token' => $token,
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
        ];
    }

    public static function validateRefreshToken(?string $token): ?object
    {
        try {
            if (empty($token) || !is_string($token)) {
                throw new \InvalidArgumentException('Invalid token provided');
            }

            return JWT::decode($token, new Key(config('app.refresh_token_secret'), 'HS512'));
        } catch (ExpiredException $e) {

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getUserId()
    {
        $request = request();
        $refToken = $request->cookie('refreshToken') ?? $request->input('refreshToken');
        $payload = static::validateRefreshToken($refToken);

        if (!$payload) {
            return null;
        }
        return $payload->sub;
    }
}
