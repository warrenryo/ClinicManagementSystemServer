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
    public function GetAllPatientsPaginated(GetPaginatedDTO $request);
    public function GetUserProfileDetails($user_details_id);

    public function GetUserMedicalRecords($user_details_id);
    public function GetUserAppointments(Request $request, $user_details_id);
}
