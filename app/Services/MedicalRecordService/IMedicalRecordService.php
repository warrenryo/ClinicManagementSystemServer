<?php

namespace App\Services\MedicalRecordService;

use App\DTO\Response\GetPaginatedDTO;
use Illuminate\Http\Request;

interface IMedicalRecordService
{
    public function AddVitalSign(Request $request, int $appointmentId);
    public function GetAppointmentMedical($appointmentId);
    public function CreateMedicalRecord(Request $request);
    public function GetAllMedicalRecordsPaginated(GetPaginatedDTO $request);
    public function ViewMedicalRecordData($medId);
    public function exportMedicalRecordPdf($medId);
    public function RequestMedicalCertificate($medical_record_id);
    public function RequestCertificatePaginated(GetPaginatedDTO $request);
    public function GetRequestCertFormDetails($reqId);
    public function AIAssistedCertificate($medId);
    public function CreateMedicalCertificate(Request $request);
    public function ViewMedicalCertificate($medicalRecId);
    public function GetMedicalCertificates($userDetailsId);
}
