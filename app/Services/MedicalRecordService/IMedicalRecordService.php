<?php

namespace App\Services\MedicalRecordService;

use Illuminate\Http\Request;

interface IMedicalRecordService
{
    public function AddVitalSign(Request $request, int $appointmentId);
    public function GetAppointmentMedical($appointmentId);
}
