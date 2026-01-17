<?php

namespace App\Services\DoctorAppointmentService;

use App\DTO\Response\GetPaginatedDTO;
use Illuminate\Http\Request;

interface IDoctorAppointmentService
{
    public function GetDoctorAppointmentPaginated(GetPaginatedDTO $request);
    public function ReassignDoctor(Request $request);
}
