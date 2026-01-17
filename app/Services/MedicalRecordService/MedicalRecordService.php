<?php

namespace App\Services\MedicalRecordService;

use App\Helpers\UserHelper;
use App\Models\Scheduling\Appointment;
use App\Response\ResponseHelper;
use Illuminate\Http\Request;

class MedicalRecordService implements IMedicalRecordService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function AddVitalSign(Request $request, int $appointmentId)
    {
        try {
            $userDetailsId = UserHelper::getUserDetailsId();

            $validatedData = $request->validate([
                'Temperature' => 'required|numeric',
                'BloodPressure' => 'required|string',
                'PulseRate' => 'required|integer',
                'Height' => 'required|numeric',
                'Weight' => 'required|numeric'
            ]);

            $appointment = Appointment::findOrFail($appointmentId);

            $appointment->medicalRecords()->create([
                'user_details_id' => $userDetailsId,
                'blood_pressure' => $validatedData['BloodPressure'],
                'pulse_rate' => $validatedData['PulseRate'],
                'temperature' => $validatedData['Temperature'],
                'height' => $validatedData['Height'],
                'weight' => $validatedData['Weight'],
            ]);

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetAppointmentMedical($appointmentId)
    {
        try {
            $appointment = Appointment::findOrFail($appointmentId);
            $record = $appointment->medicalRecords()->first();

            $intial_details = [
                'Name' => $appointment->userDetails->first_name . ' ' . $appointment->userDetails->last_name,
                'StudentDetails' => $appointment->userDetails->studentDetails()->exists()
                    ? [
                        'Course'    => $appointment->userDetails->studentDetails->course,
                        'Year'      => $appointment->userDetails->studentDetails->year_level,
                    ]
                    : null,
                'TeacherDetails'   => null,
                'StaffDetails'     => null,
                'VisitDate'        => $appointment->appointment_date,
                'VisitTime'        => $appointment->appointment_time,
                'Reason'           => $appointment->reason,
                'InitialVitalSign' => $record
                    ? [
                        'Temperature'   => $record->temperature,
                        'BloodPressure' => $record->blood_pressure,
                        'PulseRate'     => $record->pulse_rate,
                        'Height'        => $record->height,
                        'Weight'        => $record->weight,
                    ]
                    : null
            ];

            return ResponseHelper::successWData(200, "Success", $intial_details);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }
}
