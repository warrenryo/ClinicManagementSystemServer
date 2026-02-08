<?php

namespace App\Services\WalkinService;

use Illuminate\Http\Request;

interface IWalkinService
{
    public function VerifyStudentNo(Request $request);
    public function CreateWalkinAppointment(Request $request);
}
