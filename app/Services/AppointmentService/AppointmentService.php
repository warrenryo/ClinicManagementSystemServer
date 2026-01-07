<?php

namespace App\Services\AppointmentService;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Helpers\Token;
use App\Helpers\UserHelper;
use App\Models\Scheduling\Appointment;
use App\Response\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppointmentService implements IAppointmentService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function CreateAppointment(Request $request)
    {
        try {

            $userDetailsId = UserHelper::getUserDetailsId();

            $validatedData = $request->validate([
                'Date' => 'required|date',
                'Time' => 'required|string',
                'Reason' => 'required|string',
            ]);

            $appointmentDate = Carbon::parse($validatedData['Date'])
                ->toDateString();

            Appointment::create([
                'user_details_id' => $userDetailsId,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $validatedData['Time'],
                'reason' => $validatedData['Reason'],
                'status' => AppointmentStatus::PENDING->value,
                'type' => AppointmentType::SCHEDULED->value,
                'qr_token' => (string) Str::uuid()
            ]);

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }


    public function GetAppointmentDates(Request $request)
    {
        try {
            $month = $request->query('month');

            $appointments = Appointment::whereMonth('appointment_date', substr($month, 5, 2))
                ->whereYear('appointment_date', substr($month, 0, 4))
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->get();

            $grouped_appointment = $appointments->groupBy(function ($appointment) {
                return $appointment->appointment_date->format('Y-m-d');
            })->map(function ($items) {
                return $items->map(function ($appointment) {
                    return [
                        'Time' => Carbon::parse($appointment->appointment_time)->format('H:i')
                    ];
                });
            });

            return ResponseHelper::successWData(200, "Success", $grouped_appointment);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }
}
