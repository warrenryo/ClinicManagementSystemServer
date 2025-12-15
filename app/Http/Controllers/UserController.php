<?php

namespace App\Http\Controllers;

use App\Http\Requests\Requests\GetPaginatedRequest;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Response\ResponseHelper;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function UpdateProfile(Request $request)
    {
        try {
            $status = $this->userService->UpdateProfile($request);
            return ResponseHelper::getStatusResponse($status);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function GetUserDetails()
    {
        try {
            $status = $this->userService->GetUserDetails();
            return ResponseHelper::getStatusResponse($status);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function GetAllUserPaginated(GetPaginatedRequest $request)
    {
        try {
            $dto = $request->toDTO();
            $status = $this->userService->GetAllUsersPaginated($dto);
            return ResponseHelper::getStatusResponse($status);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function ToggleUserStatus($id)
    {
        try {
            $status = $this->userService->ToggleUserStatus($id);
            return ResponseHelper::getStatusResponse($status);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function GetAllUsersList()
    {
        try {
            $status = $this->userService->GetAllUsersList();
            return ResponseHelper::getStatusResponse($status);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 400);
        }
    }
}
