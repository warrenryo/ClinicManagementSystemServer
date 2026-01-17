<?php

namespace App\Services\AuthService;

use App\Enums\Course;
use App\Enums\UserRoles;
use App\Enums\YearLevel;
use App\Helpers\Token;
use App\Helpers\UserAccessHelper;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use App\Response\ResponseHelper;
use App\Services\AuthService\IAuthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class AuthService implements IAuthService
{
    public function Login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password) && $user->is_active) {

            $accessToken = Token::createToken($user);
            $refresh = Token::generateRefreshToken($user);

            $user->refresh_token = $refresh['token'];
            $user->access_token = $accessToken;
            $user->refresh_token_createdAt = $refresh['created_at'];
            $user->refresh_token_expiresAt = $refresh['expires_at'];
            $user->save();

            $result = [
                'access_token' => $accessToken,
                'refresh_token' => $refresh['token'],
                'user_access' => $user->user_access
            ];

            return ResponseHelper::successWData(200, 'Success', $result);
        }

        return ResponseHelper::errorResponse(400, 'Invalid Credentials');
    }

    public function Register(Request $request)
    {
        $existed_user = User::where('email', $request['Email'])->first();


        if ($existed_user) return ResponseHelper::errorResponse(400, 'Email Already Exist');

        DB::beginTransaction();
        try {
            $password = $request['Password'];
            if (empty($password)) {
                $lastname = strtolower($request['LastName'] ?? 'user');
                $password = $lastname . date('Y'); // e.g., SMITH2025
            }


            $user = User::create([
                'email' => $request['Email'],
                'role' => $request['SystemRole'],
                'password' => bcrypt($password),
                'user_access' => UserAccessHelper::UserDefaultAccess($request['SystemRole'])
            ]);

            $userDetails = $user->userDetails()->create([
                'first_name' => $request['FirstName'],
                'last_name' => $request['LastName'],
                'phone' => $request['Phone'],
                'address' => $request['Address'],
                'city' => $request['City'],
                'postal_code' => $request['PostalCode'],
                'profile_img' => $request['ProfileImg'] ?? null,
            ]);

            if ($request['SystemRole'] === UserRoles::STUDENTS->value) {
                $userDetails->studentDetails()->create([
                    'student_number' => $request['StudentNumber'],
                    'course' => Course::from($request['Course'])->value,
                    'year_level' => YearLevel::from($request['YearLevel'])->value,
                ]);
            }

            if ($request['SystemRole'] === UserRoles::DOCTORS->value) {
                $userDetails->doctorDetails()->create([
                    'specialization' => $request['Specialization'],
                    'license_number' => $request['LicenseNumber'],
                ]);
            }

            DB::commit();

            return ResponseHelper::successResponse();
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::errorResponse(500, "Registration Failed: {$th->getMessage()}");
        }
    }

    public function RefreshToken(Request $request)
    {
        $userId = Token::getUserId();
        if (!$userId) {
            return ResponseHelper::errorResponse(404, 'Unauthorized');
        }

        $user = User::where('id', $userId)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(400, 'Invalid Credentials');
        }

        if (now()->greaterThan($user->refresh_token_expiresAt)) {
            $user->refresh_token = null;
            $user->refresh_token_createdAt = null;
            $user->refresh_token_expiresAt = null;
            $user->save();

            return ResponseHelper::errorResponse(404, 'Refresh Token Expired');
        }

        $accessToken = Token::createToken($user);
        $refresh = Token::generateRefreshToken($user);

        $user->refresh_token = $refresh['token'];
        $user->refresh_token_createdAt = $refresh['created_at'];
        $user->refresh_token_expiresAt = $refresh['expires_at'];
        $user->save();

        $result = [
            'access_token' => $accessToken,
            'refresh_token' => $refresh['token'],
            'user_access' => $user->user_access
        ];

        return ResponseHelper::successWData(200, 'Success', $result);
    }
}
