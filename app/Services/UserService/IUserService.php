<?php

namespace App\Services\UserService;

use App\DTO\Response\GetPaginatedDTO;
use Illuminate\Http\Request;

interface IUserService
{
    public function UpdateProfile(Request $request);
    public function GetAllUsersPaginated(GetPaginatedDTO $request);
    public function ToggleUserStatus($userId);
    public function GetAllUsersList();
    public function GetAllDoctorsPaginated(GetPaginatedDTO $request);
}
