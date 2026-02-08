<?php

namespace App\Http\Controllers;

use App\Http\Requests\Requests\GetPaginatedRequest;
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

    public function GetAllMedicalRecords(GetPaginatedRequest $request)
    {
        $dto = $request->toDTO();
        $response = $this->medicalRecordService->GetAllMedicalRecordsPaginated($dto);
        return ResponseHelper::getStatusResponse($response);
    }

    public function ViewMedicalRecordData($medId)
    {
        $response = $this->medicalRecordService->ViewMedicalRecordData($medId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function exportMedicalRecordPdf($medId)
    {
        $response = $this->medicalRecordService->exportMedicalRecordPdf($medId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function RequestMedicalCertificate($medical_record_id)
    {
        $response = $this->medicalRecordService->RequestMedicalCertificate($medical_record_id);
        return ResponseHelper::getStatusResponse($response);
    }

    public function RequestCertificatePaginated(GetPaginatedRequest $request)
    {
        $toDto = $request->toDTO();
        $response = $this->medicalRecordService->RequestCertificatePaginated($toDto);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetRequestCertFormDetails($reqId)
    {
        $response = $this->medicalRecordService->GetRequestCertFormDetails($reqId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function AIAssistedCertificate($medId)
    {
        $response = $this->medicalRecordService->AIAssistedCertificate($medId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function CreateMedicalCertificate(Request $request)
    {
        $response = $this->medicalRecordService->CreateMedicalCertificate($request);
        return ResponseHelper::getStatusResponse($response);
    }

    public function ViewMedicalCertificate($medicalRecId)
    {
        $response = $this->medicalRecordService->ViewMedicalCertificate($medicalRecId);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetMedicalCertificates($userDetailsId)
    {
        $response = $this->medicalRecordService->GetMedicalCertificates($userDetailsId);
        return ResponseHelper::getStatusResponse($response);
    }
}
