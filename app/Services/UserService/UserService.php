<?php

namespace App\Services;

use App\DTO\Response\GetPaginatedDTO;
use App\DTO\Response\PaginatedTableResponse;
use App\Helpers\Token;
use App\Models\Auth\User;
use App\Models\Auth\UserDetails;
use Illuminate\Http\Request;
use App\Response\ResponseHelper;
use App\Services\UserService\IUserService;

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
            $query = User::query();

            if (!empty($request->searchValue)) {
                $query->where(function ($q) use ($request) {
                    $search = $request->searchValue;

                    $q->where('email', 'LIKE', "%{$search}%")
                        ->orWhereHas('UserDetails', function ($subQuery) use ($search) {
                            $subQuery->where('first_name', 'LIKE', "%{$search}%")
                                ->orWhere('last_name', 'LIKE', "%{$search}%");
                        });
                });
            }

            $count = $query->count();

            $users = $query
                ->orderBy('id', 'DESC')
                ->skip($request->skip)
                ->take($request->take)
                ->get();

            $result_data = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'full_name' => $user->UserDetails->first_name . ' ' . $user->UserDetails->last_name,
                    'role' => $user->role,
                    'is_active' => (bool)$user->is_active
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
}
