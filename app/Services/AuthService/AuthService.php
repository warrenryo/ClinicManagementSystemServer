<?php

namespace App\Services\AuthService;

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
        $existed_user = User::where('email', $request['email'])->first();


        if ($existed_user) return ResponseHelper::errorResponse(400, 'Email Already Exist');

        DB::beginTransaction();
        try {
            $user = User::create([
                'email' => $request['email'],
                'role' => $request['system_role'],
                'password' => bcrypt($request['password']),
                'user_access' => UserAccessHelper::UserDefaultAccess($request['system_role'])
            ]);

            $user_details_data = [
                'first_name' => $request['firstname'],
                'last_name' => $request['lastname'],
                'phone' => $request['phone'],
                'address' => $request['address'],
                'city' => $request['city'],
                'postal_code' => $request['postal_code'],
                'profile_img' => $request['profile_img']
            ];

            $user_details = $user->UserDetails()->create($user_details_data);

            if (!empty($request['churches'] && is_array($request['churches']))) {
                foreach ($request['churches'] as $church) {
                    $addChurch = [
                        'user_details_id' => $user_details->id,
                        'church_id' => $church['church_id']
                    ];

                    $user_details->UserChurches()->create($addChurch);
                }
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

        $user = User::where('id', $userId->sub)->first();
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
