<?php

namespace App\Http\Controllers;

use App\Response\ResponseHelper;
use App\Services\WalkinService\IWalkinService;
use Illuminate\Http\Request;

class WalkinController extends Controller
{
    protected $walkinService;

    public function __construct(IWalkinService $walkinService)
    {
        $this->walkinService = $walkinService;
    }

    public function VerifyStudentNo(Request $request)
    {
        $response = $this->walkinService->VerifyStudentNo($request);
        return ResponseHelper::getStatusResponse($response);
    }

    public function CreateWalkinAppointment(Request $request)
    {
        $response = $this->walkinService->CreateWalkinAppointment($request);
        return ResponseHelper::getStatusResponse($response);
    }
}
