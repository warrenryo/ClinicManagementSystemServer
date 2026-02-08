<?php

namespace App\Services\DoctorAppointmentService;

use App\DTO\Response\GetPaginatedDTO;
use App\DTO\Response\PaginatedTableResponse;
use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\ApprovalStatus;
use App\Helpers\UserHelper;
use App\Models\Auth\DoctorDetails;
use App\Models\Scheduling\Appointment;
use App\Response\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

use function PHPUnit\Framework\isEmpty;

class DoctorAppointmentService implements IDoctorAppointmentService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function GetDoctorAppointmentPaginated(GetPaginatedDTO $request)
    {
        try {
            $userDetailsId = UserHelper::getUserDetailsId();
            $doctor = DoctorDetails::where('user_details_id', $userDetailsId)->first();

            $query = Appointment::query()
                ->where('status', '!=', AppointmentStatus::CHECKUP_DONE->value)
                ->search(
                    $request->SearchValue,
                    ['reason'],
                    ['userDetails' => ['first_name', 'last_name']],
                    [],
                    ['appointment_date']
                );;

            if (!isEmpty($doctor)) {
                $query = $query->whereHas('appointmentDoctors', function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id)
                        ->where('status', ApprovalStatus::APPROVED->value);
                });
            }


            if (!empty($request->Date)) {
                $query->whereDate(
                    'appointment_date',
                    Carbon::parse($request->Date)->toDateString()
                );
            }

            if ($request->AppointmentType !== null) {
                $type = AppointmentType::tryFrom((int) $request->AppointmentType);

                if ($type) {
                    $query->where('type', $type->value);
                }
            }

            $count = $query->count();

            $appointments = $query
                ->orderBy('appointment_date', 'asc')
                ->orderBy('appointment_time', 'asc')
                ->skip($request->Skip)
                ->take($request->Take)
                ->get();

            $result_data = $appointments->map(function ($appointment) {

                $appointmentDoctor = $appointment->appointmentDoctors->first();

                $doctorName = null;

                if ($appointmentDoctor && $appointmentDoctor->doctorDetails) {
                    $doctorName =
                        $appointmentDoctor->doctorDetails->userDetails->first_name . ' ' .
                        $appointmentDoctor->doctorDetails->userDetails->last_name;
                }

                return [
                    'Id' => $appointment->id,
                    'FullName' => $appointment->userDetails()->exists()
                        ? $appointment->userDetails->first_name . ' ' . $appointment->userDetails->last_name
                        : $appointment->walkin->first_name . ' ' . $appointment->walkin->last_name,
                    'AppointmentDate' => $appointment->appointment_date->toDateString(),
                    'AppointmentTime' => $appointment->appointment_time,
                    'Doctor' => $appointmentDoctor ? [
                        'DoctorId' => $appointmentDoctor->doctor_id,
                        'FullName' => $doctorName
                    ] : null,
                    'Status' => AppointmentStatus::from($appointment->status)->value,
                    'Type' => AppointmentType::from($appointment->type)->value,
                    'HasVitalSigns' => $appointment->medicalRecords()->exists(),
                ];
            })->toArray();

            $paginated_data = new PaginatedTableResponse($result_data, $count);

            return ResponseHelper::successWData(200, "Success", $paginated_data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function ReassignDoctor(Request $request)
    {
        try {
            $data = $request->validate([
                'AppointmentId' => 'required|integer',
                'Reason' => 'required|string'
            ]);

            $appointment = Appointment::findOrFail($data['AppointmentId']);

            $appointment->update([
                'status' => AppointmentStatus::REASSIGN->value,
            ]);

            $appointment->appointmentDoctors()->update([
                'status' => ApprovalStatus::REASSIGN->value,
                'reassign_reason' => $data['Reason']
            ]);

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }
}
