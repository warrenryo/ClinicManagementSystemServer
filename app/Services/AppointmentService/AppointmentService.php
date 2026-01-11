<?php

namespace App\Services\AppointmentService;

use App\DTO\Response\GetPaginatedDTO;
use App\DTO\Response\PaginatedTableResponse;
use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Helpers\Token;
use App\Helpers\UserHelper;
use App\Models\Scheduling\Appointment;
use App\Response\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
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
                'Reason' => 'required|integer',
                'SpecifiedReason' => 'nullable|string',
            ]);

            return DB::transaction(function () use ($validatedData, $userDetailsId) {
                $appointmentDate = Carbon::parse($validatedData['Date'])->toDateString();

                // Check if user already has an appointment
                $userAppointment = Appointment::where('user_details_id', $userDetailsId)
                    ->where('appointment_date', $appointmentDate)
                    ->where('appointment_time', $validatedData['Time'])
                    ->first();

                if ($userAppointment && $userAppointment->status !== AppointmentStatus::CANCELLED->value) {
                    return ResponseHelper::errorResponse(
                        409,
                        'You already have an appointment booked for this date and time.'
                    );
                }

                $existingAppointment = Appointment::where('appointment_date', $appointmentDate)
                    ->where('appointment_time', $validatedData['Time'])
                    ->whereIn('status', [
                        AppointmentStatus::PENDING->value,
                    ])
                    ->lockForUpdate()
                    ->first();

                if ($existingAppointment) {
                    return ResponseHelper::errorResponse(
                        409,
                        'This appointment slot is already taken. Please choose another time.'
                    );
                }

                Appointment::create([
                    'user_details_id' => $userDetailsId,
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => $validatedData['Time'],
                    'reason' => $validatedData['Reason'],
                    'other_reason' => $validatedData['SpecifiedReason'],
                    'status' => AppointmentStatus::PENDING->value,
                    'type' => AppointmentType::SCHEDULED->value,
                    'qr_token' => (string) Str::uuid(),
                ]);

                return ResponseHelper::successResponse(200, "Appointment booked successfully");
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return ResponseHelper::errorResponse(
                    409,
                    'This appointment slot is already taken. Please choose another time.'
                );
            }
            return ResponseHelper::errorResponse(500, 'Database error occurred');
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }


    public function GetAppointmentDates(Request $request)
    {
        try {
            $month = $request->query('month');

            $appointments = Appointment::whereMonth('appointment_date', substr($month, 5, 2))
                ->whereYear('appointment_date', substr($month, 0, 4))
                ->where('status', '!=', AppointmentStatus::CANCELLED->value)
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

    public function GetUserAppointments(GetPaginatedDTO $request)
    {
        try {
            $userDetailsId = UserHelper::getUserDetailsId();
            $query = Appointment::where('user_details_id', $userDetailsId);

            $count = $query->count();

            $appointments = $query
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->skip($request->Skip)
                ->take($request->Take)
                ->get();

            $result_data = $appointments->map(function ($appointment) {
                return [
                    'Id' => $appointment->id,
                    'AppointmentDate' => $appointment->appointment_date->toDateString(),
                    'AppointmentTime' => $appointment->appointment_time,
                    'Status' => AppointmentStatus::from($appointment->status)->value,
                    'Type' => AppointmentType::from($appointment->type)->value,
                ];
            })->toArray();

            $paginated_data = new PaginatedTableResponse($result_data, $count);

            return ResponseHelper::successWData(200, "Success", $paginated_data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetUserAppointmentDetails($appointmentId)
    {
        try {

            $appointment = Appointment::where('id', $appointmentId)
                ->first();

            if (!$appointment) {
                return ResponseHelper::errorResponse(404, "Appointment not found");
            }

            $result_data = [
                'Id' => $appointment->id,
                'AppointmentDate' => $appointment->appointment_date->toDateString(),
                'AppointmentTime' => $appointment->appointment_time,
                'Reason' => $appointment->reason,
                'OtherReason' => $appointment->other_reason,
                'Status' => AppointmentStatus::from($appointment->status)->value,
                'Type' => AppointmentType::from($appointment->type)->value,
                'QrToken' => $appointment->qr_token,
            ];

            return ResponseHelper::successWData(200, "Success", $result_data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetOverallAppointments(GetPaginatedDTO $request)
    {
        try {
            $query = Appointment::query();

            $count = $query->count();

            $appointments = $query
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->skip($request->Skip)
                ->take($request->Take)
                ->get();

            $result_data = $appointments->map(function ($appointment) {
                return [
                    'Id' => $appointment->id,
                    'FullName' => $appointment->userDetails->first_name . ' ' . $appointment->userDetails->last_name,
                    'AppointmentDate' => $appointment->appointment_date->toDateString(),
                    'AppointmentTime' => $appointment->appointment_time,
                    'Status' => AppointmentStatus::from($appointment->status)->value,
                    'Type' => AppointmentType::from($appointment->type)->value,
                ];
            })->toArray();

            $paginated_data = new PaginatedTableResponse($result_data, $count);

            return ResponseHelper::successWData(200, "Success", $paginated_data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetAppointmentsByDate($date)
    {
        try {

            $appointments = Appointment::where('appointment_date', $date)
                ->orderBy('appointment_time')
                ->get();

            $result_data = $appointments->map(function ($appointment) {
                return [
                    'Id' => $appointment->id,
                    'Name' => $appointment->userDetails->first_name . ' ' . $appointment->userDetails->last_name,
                    'AppointmentDate' => $appointment->appointment_date->toDateString(),
                    'AppointmentTime' => $appointment->appointment_time,
                    'Reason' => $appointment->reason,
                    'OtherReason' => $appointment->other_reason,
                    'Status' => AppointmentStatus::from($appointment->status)->value,
                    'Type' => AppointmentType::from($appointment->type)->value,
                    'QrToken' => $appointment->qr_token,
                ];
            })->toArray();

            return ResponseHelper::successWData(200, "Success", $result_data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetAppointmentCalendarCounts($month, $year)
    {
        try {
            $appointments = Appointment::whereMonth('appointment_date', $month)
                ->whereYear('appointment_date', $year)
                ->get();

            $date_counts = $appointments->groupBy(function ($appointment) {
                return $appointment->appointment_date->format('Y-m-d');
            })->map(function ($items) {
                return $items->groupBy('status')->map(function ($statusItems, $status) {
                    return [
                        'count' => count($statusItems),
                        'label' => AppointmentStatus::from($status)->value,
                    ];
                });
            });

            return ResponseHelper::successWData(200, "Success", $date_counts);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function SetAppointmentStatus($appointmentId, $status)
    {
        try {
            $appointment = Appointment::find($appointmentId);

            if (!$appointment) {
                return ResponseHelper::errorResponse(404, "Appointment not found");
            }

            $appointment->status = $status;
            $appointment->save();

            return ResponseHelper::successResponse(200, "Appointment status updated successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function RescheduleAppointment(Request $request, $appointmentId)
    {
        try {
            $validatedData = $request->validate([
                'NewDate' => 'required|date',
                'NewTime' => 'required|string',
                'RescheduleReason' => 'required|string',
            ]);

            return DB::transaction(function () use ($validatedData, $appointmentId) {
                $appointment = Appointment::find($appointmentId);

                if (!$appointment) {
                    return ResponseHelper::errorResponse(404, "Appointment not found");
                }

                $appointmentDate = Carbon::parse($validatedData['NewDate'])->toDateString();

                $existingAppointment = Appointment::where('appointment_date', $appointmentDate)
                    ->where('appointment_time', $validatedData['NewTime'])
                    ->whereIn('status', [
                        AppointmentStatus::PENDING->value,
                    ])
                    ->lockForUpdate()
                    ->first();

                if ($existingAppointment) {
                    return ResponseHelper::errorResponse(
                        409,
                        'This appointment slot is already taken. Please choose another time.'
                    );
                }

                $appointment->appointment_date = $appointmentDate;
                $appointment->appointment_time = $validatedData['NewTime'];
                $appointment->status = AppointmentStatus::PENDING->value;
                $appointment->type = AppointmentType::RESCHEDULED->value;
                $appointment->reschedule_reason = $validatedData['RescheduleReason'];
                $appointment->save();

                return ResponseHelper::successResponse(200, "Appointment rescheduled successfully");
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return ResponseHelper::errorResponse(
                    409,
                    'This appointment slot is already taken. Please choose another time.'
                );
            }
            return ResponseHelper::errorResponse(500, 'Database error occurred');
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }
}
