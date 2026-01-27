<?php

namespace App\Services\MedicalRecordService;

use App\Enums\AppointmentStatus;
use App\Enums\ApprovalStatus;
use App\Helpers\UserHelper;
use App\Models\Inventory\Products;
use App\Models\Medical\MedicalRecords;
use App\Models\Scheduling\Appointment;
use App\Response\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

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
                'RecordId' => $record->id ?? null,
                'UserDetailsId' => $appointment->userDetails->id,
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
                        'AppointmentId' => $appointment->id,
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

    public function CreateMedicalRecord(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'RecordId' => 'nullable|integer',
                'UserDetailsId' => 'required|integer',
                'Symptoms' => 'nullable|string',

                'VitalSigns' => 'nullable|array',
                'VitalSigns.AppointmentId' => 'nullable|integer',
                'VitalSigns.Temperature' => 'nullable|numeric',
                'VitalSigns.BloodPressure' => 'nullable|string',
                'VitalSigns.PulseRate' => 'nullable|integer',
                'VitalSigns.Height' => 'nullable|numeric',
                'VitalSigns.Weight' => 'nullable|numeric',

                'Findings' => 'nullable|string',
                'Remarks' => 'nullable|string',

                'ActionTaken' => 'nullable|array',
                'ActionTaken.*' => 'integer',

                'ItemsProvided' => 'nullable|array',
                'ItemsProvided.*.Product' => 'required|array',
                'ItemsProvided.*.Product.Id' => 'required|integer|exists:products,id',
                'ItemsProvided.*.Quantity' => 'required|integer|min:1',
                'ItemsProvided.*.Notes' => 'nullable|string',
            ]);

            DB::transaction(function () use ($validatedData, &$medical_record) {

                $medical_record = isset($validatedData['RecordId'])
                    ? MedicalRecords::find($validatedData['RecordId'])
                    : null;

                $payload = [
                    'appointment_id'   => data_get($validatedData, 'VitalSigns.AppointmentId'),
                    'user_details_id'  => $validatedData['UserDetailsId'],
                    'temperature'      => data_get($validatedData, 'VitalSigns.Temperature'),
                    'blood_pressure'   => data_get($validatedData, 'VitalSigns.BloodPressure'),
                    'pulse_rate'       => data_get($validatedData, 'VitalSigns.PulseRate'),
                    'height'           => data_get($validatedData, 'VitalSigns.Height'),
                    'weight'           => data_get($validatedData, 'VitalSigns.Weight'),
                    'symptoms'         => $validatedData['Symptoms'] ?? null,
                    'action_taken'     => $validatedData['ActionTaken'] ?? [],
                    'remarks'          => $validatedData['Remarks'] ?? null,
                    'findings'         => $validatedData['Findings'] ?? null,
                    'is_done'          => true
                ];

                if ($medical_record) {
                    $medical_record->update($payload);

                    $medical_record->medicalItems()->delete();
                } else {
                    $medical_record = MedicalRecords::create($payload);
                }

                if (!empty($validatedData['ItemsProvided'])) {
                    foreach ($validatedData['ItemsProvided'] as $item) {
                        $medical_record->medicalItems()->create([
                            'products_id' => $item['Product']['Id'],
                            'quantity'   => $item['Quantity'],
                            'notes'      => $item['Notes'] ?? null,
                        ]);

                        $product = Products::find($item['Product']['Id']);
                        if ($product) {
                            $product->quantity -= $item['Quantity'];
                            $product->save();
                        }
                    }
                }

                $appointment = $medical_record->appointment;
                if ($appointment) {
                    $appointment->status = AppointmentStatus::CHECKUP_DONE->value;
                    $appointment->save();
                }
            });

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }
}
