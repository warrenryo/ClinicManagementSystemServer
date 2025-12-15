<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Response\ResponseHelper;
use App\Services\AuthService\IAuthService;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(IAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function Login(Request $request)
    {
        $status = $this->authService->Login($request);
        if ($status['status_code'] >= 400) {
            return ResponseHelper::getStatusResponse($status);
        }

        $store_cookie = Cookie::make('refreshToken', $status['data']['refresh_token'], 10080, null, null, true, true, false, 'None');

        return response()->json($status)->cookie($store_cookie);
    }

    public function Register(Request $request)
    {
        $status = $this->authService->Register($request);
        return ResponseHelper::getStatusResponse($status);
    }

    public function RefreshToken(Request $request)
    {
        $status = $this->authService->RefreshToken($request);
        if ($status['status_code'] >= 400) {
            return ResponseHelper::getStatusResponse($status);
        }

        $store_cookie = Cookie::make('refreshToken', $status['data']['refresh_token'], 10080, null, null, true, true, false, 'None');

        return response()->json($status)->cookie($store_cookie);
    }

    public function Logout()
    {
        $expiredCookie = Cookie::make('refreshToken', '', -1, '/', null, true, true, false, 'None');
        return response()->json(['message' => 'Logged out'])->withCookie($expiredCookie);
    }
}
