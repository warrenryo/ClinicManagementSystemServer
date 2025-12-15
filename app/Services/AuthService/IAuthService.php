<?php

namespace App\Services\AuthService;

use Illuminate\Http\Request;

interface IAuthService
{
    public function Login(Request $request);
    public function Register(Request $request);
    public function RefreshToken(Request $request);
}
