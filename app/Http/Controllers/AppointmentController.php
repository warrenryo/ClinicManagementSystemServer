<?php

namespace App\Http\Controllers;

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
}
