<?php

namespace App\Http\Controllers;

use App\Response\ResponseHelper;
use App\Services\MedicalRecordService\IMedicalRecordService;
use Illuminate\Http\Request;

class MedicalRecordsController extends Controller
{
    protected $medicalRecordService;
    public function __construct(IMedicalRecordService $medicalRecordService)
    {
        $this->medicalRecordService = $medicalRecordService;
    }

    public function AddVitalSign(Request $request, int $appointmentId)
    {
        $response = $this->medicalRecordService->AddVitalSign($request, $appointmentId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetAppointmentMedical($appointmentId)
    {
        $response = $this->medicalRecordService->GetAppointmentMedical($appointmentId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function CreateMedicalRecord(Request $request)
    {
        $response = $this->medicalRecordService->CreateMedicalRecord($request);
        return ResponseHelper::getStatusResponse($response);
    }
}
