<?php

namespace App\Helpers;

use App\Enums\UserAccess;
use App\Enums\UserRoles;
use App\Models\Auth\User;

class UserAccessHelper
{
    public static function UserDefaultAccess($role)
    {
        $roleEnum = UserRoles::tryFrom($role);
        if ($roleEnum === null) {
            return [];
        }

        if ($roleEnum === UserRoles::SUPERUSER) {
            $default_access = [
                UserAccess::DASHBOARD->value,

            ];

            return $default_access;
        } else if ($roleEnum === UserRoles::USERS) {
            return [
                UserAccess::DASHBOARD->value,

            ];
        }

        return [];
    }
}
