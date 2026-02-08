<?php

namespace App\Services\WalkinService;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\UserRoles;
use App\Models\Auth\EmployeeDetails;
use App\Models\Scheduling\Appointment;
use App\Models\Scheduling\Walkin;
use App\Models\Students\StudentDetails;
use App\Response\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalkinService implements IWalkinService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function VerifyStudentNo(Request $request)
    {
        try {
            return match ($request->UserRole) {
                UserRoles::STUDENTS->value =>
                StudentDetails::where('student_number', $request->IdentificationNumber)->exists()
                    ? ResponseHelper::successResponse()
                    : ResponseHelper::errorResponse(),

                UserRoles::TEACHERS->value, UserRoles::STAFF->value =>
                EmployeeDetails::where('employee_no', $request->IdentificationNumber)->exists()
                    ? ResponseHelper::successResponse()
                    : ResponseHelper::errorResponse(),

                default => ResponseHelper::errorResponse(),
            };
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function CreateWalkinAppointment(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'appointmentReason'     => 'required|integer',
                'birthDate'             => 'nullable|date',
                'firstName'             => 'nullable|string',
                'lastName'              => 'nullable|string',
                'otherReason'           => 'nullable|string',
                'role'                  => 'required|integer',
                'identificationNumber'  => 'nullable|string',
            ]);

            $userDetailsId = null;
            $walkinId = null;

            if ($validated['role'] === UserRoles::STUDENTS->value) {
                $userDetailsId = StudentDetails::where(
                    'student_number',
                    $validated['identificationNumber']
                )->value('user_details_id');
            }

            if ($validated['role'] === UserRoles::VISITOR->value) {

                $walkin = Walkin::where('first_name', $validated['firstName'])
                    ->where('last_name', $validated['lastName'])
                    ->whereDate(
                        'birthdate',
                        Carbon::parse($validated['birthDate'])->toDateString()
                    )
                    ->first();

                if (!$walkin) {
                    $walkin = Walkin::create([
                        'first_name' => $validated['firstName'],
                        'last_name'  => $validated['lastName'],
                        'birthdate'  => $validated['birthDate']
                            ? Carbon::parse($validated['birthDate'])->format('Y-m-d')
                            : null,
                    ]);
                }

                $walkinId = $walkin->id;
            }

            Appointment::create([
                'user_details_id'  => $userDetailsId,
                'walkin_id'        => $walkinId,
                'appointment_date' => Carbon::today(),
                'appointment_time' => now()->format('H:i:s'),
                'reason'           => $validated['appointmentReason'],
                'other_reason'     => $validated['otherReason'],
                'status'           => AppointmentStatus::APPROVED->value,
                'type'             => AppointmentType::WALK_IN->value,
                'qr_token'         => (string) Str::uuid(),
            ]);

            DB::commit();

            return ResponseHelper::successResponse(200, 'Success');
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }
}
