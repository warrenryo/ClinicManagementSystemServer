<?php

namespace App\Http\Controllers;

use App\Http\Requests\Requests\GetPaginatedRequest;
use App\Response\ResponseHelper;
use App\Services\DoctorAppointmentService\IDoctorAppointmentService;
use Illuminate\Http\Request;

class DoctorAppointmentController extends Controller
{
    protected $doctorAppointmentService;

    public function __construct(IDoctorAppointmentService $doctorAppointmentService)
    {
        $this->doctorAppointmentService = $doctorAppointmentService;
    }

    public function GetDoctorAppointmentPaginated(GetPaginatedRequest $request)
    {
        $toDto = $request->toDTO();
        $response = $this->doctorAppointmentService->GetDoctorAppointmentPaginated($toDto);
        return ResponseHelper::getStatusResponse($response);
    }

    public function ReassignDoctor(Request $request)
    {
        $response = $this->doctorAppointmentService->ReassignDoctor($request);
        return ResponseHelper::getStatusResponse($response);
    }
}
