<?php

namespace App\Http\Controllers;

use App\Http\Requests\Requests\GetPaginatedRequest;
use App\Response\ResponseHelper;
use App\Services\AppointmentService\IAppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentService;
    public function __construct(IAppointmentService $appointment)
    {
        $this->appointmentService = $appointment;
    }

    public function CreateAppointment(Request $request)
    {
        $response = $this->appointmentService->CreateAppointment($request);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetAppointmentDates(Request $request)
    {
        $response = $this->appointmentService->GetAppointmentDates($request);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetUserAppointments(GetPaginatedRequest $request)
    {
        $toDto = $request->toDTO();
        $response = $this->appointmentService->GetUserAppointments($toDto);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetUserAppointmentDetails($appointmentId)
    {
        $response = $this->appointmentService->GetUserAppointmentDetails($appointmentId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetOverallAppointments(GetPaginatedRequest $request)
    {
        $toDto = $request->toDTO();
        $response = $this->appointmentService->GetOverallAppointments($toDto);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetAppointmentsByDate($date)
    {
        $response = $this->appointmentService->GetAppointmentsByDate($date);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetAppointmentCalendarCounts($month, $year)
    {
        $response = $this->appointmentService->GetAppointmentCalendarCounts($month, $year);
        return ResponseHelper::getStatusResponse($response);
    }

    public function SetAppointmentStatus($appointmentId, $status)
    {
        $response = $this->appointmentService->SetAppointmentStatus($appointmentId, $status);
        return ResponseHelper::getStatusResponse($response);
    }

    public function RescheduleAppointment(Request $request, $appointmentId)
    {
        $response = $this->appointmentService->RescheduleAppointment($request, $appointmentId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function AssignDoctorToAppointment(Request $request)
    {
        $response = $this->appointmentService->AssignDoctorToAppointment($request);
        return ResponseHelper::getStatusResponse($response);
    }
}
