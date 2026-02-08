<?php

namespace App\Services\UserService;

use App\DTO\Response\GetPaginatedDTO;
use App\DTO\Response\PaginatedTableResponse;
use App\Enums\DoctorSpecialization;
use App\Enums\UserRoles;
use App\Helpers\Token;
use App\Models\Auth\DoctorDetails;
use App\Models\Auth\User;
use App\Models\Auth\UserDetails;
use App\Models\Scheduling\Walkin;
use Illuminate\Http\Request;
use App\Response\ResponseHelper;
use App\Services\UserService\IUserService;
use Carbon\Carbon;

class UserService implements IUserService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function GetUserDetails()
    {
        try {
            $userClaims = Token::getUserId();
            if (!$userClaims) {
                return ResponseHelper::errorResponse(401, 'Unauthorized');
            }

            $user = User::where('id', $userClaims->sub)
                ->with('UserDetails')
                ->first();
            if (!$user) {
                return ResponseHelper::errorResponse(404, 'User not found');
            }

            $result = $this->SerializeUserDetails($user);

            return ResponseHelper::successWData(200, 'Success', $result);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function SerializeUserDetails($user)
    {
        return [
            'email' => $user->email,
            'role' => $user->role->name,
            'firstname' => $user->UserDetails->first_name,
            'lastname' => $user->UserDetails->last_name,
            'phone' => $user->UserDetails->phone,
            'address' => $user->UserDetails->address,
            'city' => $user->UserDetails->city,
            'postal_code' => $user->UserDetails->postal_code,
            'profile_img' => !empty($user->UserDetails->profile_img)
                ? $user->UserDetails->profile_img
                : 'https://mighty.tools/mockmind-api/content/cartoon/10.jpg',
        ];
    }

    public function UpdateProfile(Request $request)
    {
        try {
            $userClaims = Token::getUserId();
            if (!$userClaims) {
                return ResponseHelper::errorResponse(401, 'Unauthorized');
            }

            $user = User::where('id', $userClaims->sub)->first();
            if (!$user) {
                return ResponseHelper::errorResponse(404, 'User not found');
            }

            $user->UserDetails()->update([
                'first_name' => $request['firstname'],
                'last_name' => $request['lastname'],
                'phone' => $request['phone'],
                'address' => $request['address'],
                'postal_code' => $request['postal_code'],
                'city' => $request['city'],
                'profile_img' => $request['profile_img'] ?? '',
            ]);

            return ResponseHelper::successResponse();
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetAllUsersPaginated(GetPaginatedDTO $request)
    {
        try {

            $query = User::query()
                ->search(
                    $request->SearchValue,
                    ['email', 'role'],
                    ['userDetails' => ['first_name', 'last_name']],
                    ['' => ['role' => UserRoles::class]]
                );

            // if (!empty($request->searchValue)) {
            //     $query->where(function ($q) use ($request) {
            //         $search = $request->searchValue;

            //         $q->where('email', 'LIKE', "%{$search}%")
            //             ->orWhereHas('UserDetails', function ($subQuery) use ($search) {
            //                 $subQuery->where('first_name', 'LIKE', "%{$search}%")
            //                     ->orWhere('last_name', 'LIKE', "%{$search}%");
            //             });
            //     });
            // }

            $count = $query->count();

            $users = $query
                ->orderBy('created_at', 'desc')
                ->skip($request->Skip)
                ->take($request->Take)
                ->get();

            $result_data = $users->map(function ($user) {
                return [
                    'Id' => $user->id,
                    'Email' => $user->email,
                    'FullName' => $user->UserDetails->first_name . ' ' . $user->UserDetails->last_name,
                    'Role' => $user->role,
                    'Active' => (bool)$user->is_active
                ];
            })->toArray();

            $paginated_response = new PaginatedTableResponse($result_data, $count);

            return ResponseHelper::successWData(200, "Success", $paginated_response);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function ToggleUserStatus($userId)
    {
        try {
            $user = User::where('id', $userId)->first();
            if (!$user) {
                return ResponseHelper::errorResponse(404, 'User not found');
            }

            $user->is_active = !$user->is_active;
            $user->save();

            return ResponseHelper::successResponse();
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetAllUsersList()
    {
        try {
            $users = UserDetails::get();

            $result = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'image' => !empty($user->profile_img)
                        ? $user->profile_img
                        : 'https://mighty.tools/mockmind-api/content/cartoon/10.jpg',
                    'name' => $user->first_name . ' ' . $user->last_name,
                ];
            });

            return ResponseHelper::successWData(200, "Success", $result);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetAllDoctorsPaginated(GetPaginatedDTO $request)
    {
        try {
            $query = DoctorDetails::query()
                ->search(
                    $request->SearchValue,
                    ['license_number', 'specialization'],
                    ['userDetails' => ['first_name', 'last_name']],
                    [
                        '' => [
                            'specialization' => DoctorSpecialization::class
                        ]
                    ],
                );

            $count = $query->count();

            $doctors = $query
                ->orderBy('created_at', 'desc')
                ->skip($request->Skip)
                ->take($request->Take)
                ->get();

            $result_data = $doctors->map(function ($doctor) {
                return [
                    'Id' => $doctor->id,
                    'FullName' => $doctor->userDetails->first_name . ' ' . $doctor->userDetails->last_name,
                    'Specialization' => $doctor->specialization,
                    'ImageUrl' => !empty($doctor->userDetails->profile_img)
                        ? $doctor->userDetails->profile_img
                        : null,
                    'LicenseNumber' => $doctor->license_number,
                ];
            })->toArray();

            $paginated_response = new PaginatedTableResponse($result_data, $count);

            return ResponseHelper::successWData(200, "Success", $paginated_response);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetAllPatientsPaginated(GetPaginatedDTO $request)
    {
        try {
            $userQuery = UserDetails::query()
                ->whereHas('user', function ($q) {
                    $q->whereNotIn('role', [
                        UserRoles::DOCTORS->value,
                        UserRoles::STAFF->value,
                        UserRoles::SUPERUSER->value,
                        UserRoles::PROCUREMENT->value,
                    ]);
                })
                ->when($request->SearchValue, function ($q) use ($request) {
                    $q->where(function ($q2) use ($request) {
                        $q2->where('first_name', 'like', "%{$request->SearchValue}%")
                            ->orWhere('last_name', 'like', "%{$request->SearchValue}%");
                    });
                });

            $users = $userQuery->get()->map(function ($user) {
                return [
                    'Id'          => $user->id,
                    'FullName'    => $user->first_name . ' ' . $user->last_name,
                    'Role'        => $user->user->role,
                    'Birthdate'   => null,
                    'PatientType' => 'USER PATIENT',
                ];
            });

            $walkinQuery = Walkin::query()
                ->when($request->SearchValue, function ($q) use ($request) {
                    $q->where(function ($q2) use ($request) {
                        $q2->where('first_name', 'like', "%{$request->SearchValue}%")
                            ->orWhere('last_name', 'like', "%{$request->SearchValue}%");
                    });
                });

            $walkins = $walkinQuery->get()->map(function ($walkin) {
                return [
                    'Id'          => $walkin->id,
                    'FullName'    => $walkin->first_name . ' ' . $walkin->last_name,
                    'Role'        => null,
                    'Birthdate'   => $walkin->birthdate,
                    'PatientType' => 'WALKIN PATIENT',
                ];
            });

            $patients = $users->merge($walkins);

            $patients = $patients->sortBy('FullName')->values();

            $total = $patients->count();
            $data = $patients->slice($request->Skip, $request->Take)->values()->toArray();

            $paginatedResponse = new PaginatedTableResponse($data, $total);

            return ResponseHelper::successWData(200, "Success", $paginatedResponse);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function GetUserProfileDetails($user_details_id)
    {
        try {
            $user_details = UserDetails::findOrFail($user_details_id);

            $data = [
                'UserDetailsId' => $user_details->id,
                'FullName' => $user_details->first_name . ' ' . $user_details->last_name,
                'Role' => $user_details->user->role,
                'Email' => $user_details->user->email,
                'Phone' => $user_details->phone,
                'Address' => $user_details->address,
                'DateOfBirth' => $user_details->birth_date,
                'Gender' => $user_details->gender,
                'AvatarUrl' => $user_details->profile_img,
                'TeacherDetails' => $user_details->employeeDetails()->exists()
                    ? [
                        'Department' => $user_details->employeeDetails->department,
                        'Position' => $user_details->employeeDetails->position,
                    ] : null,
                'StudentDetails' => $user_details->studentDetails()->exists()
                    ? [
                        'StudentNo' => $user_details->studentDetails->student_number,
                        'Course' => $user_details->studentDetails->course,
                        'YearLevel' => $user_details->studentDetails->year_level
                    ] : null,
            ];

            return ResponseHelper::successWData(200, "Success", $data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function GetUserMedicalRecords($user_details_id)
    {
        try {
            $user_details = UserDetails::findOrFail($user_details_id);

            $data = $user_details->medicalRecords()->exists() ? $user_details->medicalRecords->map(function ($med) {
                return [
                    'recordId' => $med->id,
                    'referenceNo' => $med->reference_no,
                    'visitDate' => $med->appointment->appointment_date,
                    'visitTime' => $med->appointment->appointment_time,
                    'reason' => $med->appointment->reason,
                    'doctor' => $med->doctorDetails->userDetails->first_name . ' ' . $med->doctorDetails->userDetails->last_name,
                    'findings' => $med->findings,
                    'createdAt' => $med->created_at
                ];
            })->toArray() : null;

            return ResponseHelper::successWData(200, "Success", $data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function GetUserAppointments(Request $request, $user_details_id)
    {
        try {

            $month = $request->query('month');
            $year  = $request->query('year');

            $userDetails = UserDetails::findOrFail($user_details_id);

            $appointments = $userDetails->appointment()
                ->whereMonth('appointment_date', $month)
                ->whereYear('appointment_date', $year)
                ->get()
                ->map(function ($apt) {
                    return [
                        'appointmentId' => $apt->id,
                        'date' => $apt->appointment_date,
                        'time' => $apt->appointment_time,
                        'reason' => $apt->reason,
                        'status' => $apt->status,
                        'doctor' => null,
                        'notes' => $apt->notes,
                    ];
                });

            return ResponseHelper::successWData(200, "Success", $appointments);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }
}
