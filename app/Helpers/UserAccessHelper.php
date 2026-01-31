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
                UserAccess::USERS->value,
                UserAccess::OVERALL_APPOINTMENTS->value,
                UserAccess::TODAYS_APPOINTMENT->value,
                UserAccess::ADD_VITAL_SIGN->value,
                UserAccess::ALL_PRODUCTS->value,
                UserAccess::MODIFY_PRODUCTS->value,
            ];

            return $default_access;
        } else if ($roleEnum === UserRoles::STUDENTS) {
            return [
                UserAccess::STUDENT_DASHBOARD->value,
                UserAccess::APPOINTMENTS->value,
            ];
        } else if ($roleEnum === UserRoles::DOCTORS) {
            return [
                UserAccess::DOCTOR_DASHBOARD->value,
                UserAccess::DOCTOR_APPOINTMENTS->value,
            ];
        } else if ($roleEnum === UserRoles::STAFF) {
            return [
                UserAccess::STAFF_DASHBOARD->value,
                UserAccess::OVERALL_APPOINTMENTS->value,
                UserAccess::TODAYS_APPOINTMENT->value,
                UserAccess::ADD_VITAL_SIGN->value,
            ];
        } else if ($roleEnum === UserRoles::PROCUREMENT) {
            return [
                UserAccess::PROCUREMENT_APPROVAL->value,
            ];
        }

        return [];
    }
}
